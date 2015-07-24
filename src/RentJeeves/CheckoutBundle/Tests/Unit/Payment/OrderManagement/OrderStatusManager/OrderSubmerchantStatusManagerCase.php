<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderSubmerchantStatusManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;
use RentJeeves\TestBundle\BaseTestCase;

class OrderSubmerchantStatusManagerCase extends BaseTestCase
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    const AMOUNT_OTHER = 100;

    /**
     * @param bool $withBaseMethods
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\CoreBundle\Mailer\Mailer
     */
    protected function getMailerMock($withBaseMethods = false)
    {
        $methods = $withBaseMethods ? ['sendBaseLetter', 'sendEmail'] : [];

        return $this->getMock(
            '\RentJeeves\CoreBundle\Mailer\Mailer',
            $methods,
            [$this->getContainer()]
        );
    }

    /**
     * @return \Monolog\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }

    /**
     * @param string $paymentType
     * @param string $operationType
     * @param int $countOperations
     * @param bool $withOtherOperation
     * @param int $amountMainOperations
     * @return OrderSubmerchant
     */
    protected function createOrder(
        $paymentType = OrderPaymentType::CARD,
        $operationType = OperationType::RENT,
        $countOperations = 1,
        $withOtherOperation = false,
        $amountMainOperations = 500
    ) {
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');

        $contract = $this->getContract($startAt, $finishAt);
        $operations = $contract->getOperations();
        $this->assertTrue(($operations->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));

        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setPaymentType($paymentType);

        for ($i = 0; $i < $countOperations; $i++) {
            $operation = new Operation();
            $operation->setContract($contract);
            $operation->setAmount($amountMainOperations);
            $operation->setGroup($contract->getGroup());
            $operation->setType($operationType);
            $paidFor = new DateTime();
            $operation->setPaidFor($paidFor);
            $operation->setOrder($order);
            $this->getEntityManager()->persist($operation);

            $order->setSum($amountMainOperations + $order->getSum());
        }

        if ($withOtherOperation) {
            $operation = new Operation();
            $operation->setContract($contract);
            $operation->setAmount(self::AMOUNT_OTHER);
            $operation->setGroup($contract->getGroup());
            $operation->setType(OperationType::OTHER);
            $paidFor = new DateTime();
            $operation->setPaidFor($paidFor);
            $operation->setOrder($order);
            $this->getEntityManager()->persist($operation);

            $order->setSum(100 + $order->getSum());
        }

        if (!$order->getSum()) {
            $order->setSum(500);
        }

        $this->getEntityManager()->persist($order);

        return $order;
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage It's not allowed to set "reissued" status to order submerchant type
     */
    public function setReissued()
    {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $statusManager->setReissued($this->createOrder());
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage It's not allowed to set "sending" status to order submerchant type
     */
    public function setSending()
    {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $statusManager->setSending($this->createOrder());
    }

    /**
     * @test
     */
    public function setCompleteShouldSetStatusComplete()
    {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder();

        $this->assertNotEquals(
            OrderStatus::COMPLETE,
            $order->getStatus(),
            'Order should have status not equals "complete"'
        );

        $statusManager->setComplete($order);

        $this->assertEquals(
            OrderStatus::COMPLETE,
            $order->getStatus(),
            'Order should be updated to "complete" status'
        );
    }

    /**
     * @return array
     */
    public function updateBalanceForContractDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT, 2, true, 500, 8999.89, -1100],
            [OrderPaymentType::CARD, OperationType::RENT, 0, true, 500, 9999.89, -100],
            [OrderPaymentType::CARD, OperationType::REPORT, 1, false, 500, 9999.89, 0],
            [OrderPaymentType::CASH, OperationType::CHARGE, 1, true, 500, 9999.89, -100],
            [OrderPaymentType::BANK, OperationType::RENT, 1, true, 500, 9499.89, -600],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param float $resultBalance
     * @param float $resultIntegratedBalance
     *
     * @test
     * @dataProvider updateBalanceForContractDataProvider
     * Should work with any payment types of order
     * Should minus sum of all others and rent operations from integratedBalance
     * and from balance only rent operations
     */
    public function setCompleteShouldUpdateBalanceForContractIntegratedGroup(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $resultBalance,
        $resultIntegratedBalance
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );

        $this->assertTrue(
            $order->getContract()->getGroupSettings()->getIsIntegrated(),
            'Group is not integrated, please check fixtures'
        );
        $this->assertEquals(
            9999.89,
            $order->getContract()->getBalance(),
            sprintf(
                'Contract with id #%s has incorrect balance, please check fixtures',
                $order->getContract()->getId()
            )
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            sprintf(
                'Contract with id #%s has incorrect integrated balance, please check fixtures',
                $order->getContract()->getId()
            )
        );

        $statusManager->setComplete($order);

        $this->assertEquals(
            $resultBalance,
            $order->getContract()->getBalance(),
            'Contract for integration group should update balance whole sum of all only rent operations'
        );
        $this->assertEquals(
            $resultIntegratedBalance,
            $order->getContract()->getIntegratedBalance(),
            'Contract for integration group should update integratedBalance whole sum of all rent operations' .
            ' and all other operations'
        );
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param float $resultBalance
     *
     * @test
     * @dataProvider updateBalanceForContractDataProvider
     * Should work with any payment types of order
     * Should minus sum of all rent operations from balance
     * and integratedBalance shouldn't be changed
     */
    public function setCompleteShouldUpdateBalanceForContractNotIntegratedGroup(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $resultBalance
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );

        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(25);
        $this->assertNotNull($group, 'Group with id 25 can\'t be found, please check fixtures');
        $this->assertFalse(
            $group->getGroupSettings()->getIsIntegrated(),
            'Group is integrated, please check fixtures'
        );
        $order->getContract()->setGroup($group);
        $this->getEntityManager()->persist($order->getContract());
        $this->assertEquals(
            9999.89,
            $order->getContract()->getBalance(),
            sprintf(
                'Contract with id #%s has incorrect balance, please check fixtures',
                $order->getContract()->getId()
            )
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            sprintf(
                'Contract with id #%s has incorrect integrated balance, please check fixtures',
                $order->getContract()->getId()
            )
        );

        $statusManager->setComplete($order);

        $this->assertEquals(
            $resultBalance,
            $order->getContract()->getBalance(),
            'Contract for not integration group should update balance whole sum of all only rent operations'
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            'IntegratedBalance on contract for not integration group shouldn\'t be updated'
        );
    }

    /**
     * @return array
     */
    public function shouldShiftContractPaidToDateDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT, 1, false, 999999.99, '+1 month', 'Y-m-d'],
            [OrderPaymentType::BANK, OperationType::RENT, 2, true, 999999.99, '+2 month', 'Y-m-d'],
            [OrderPaymentType::BANK, OperationType::CHARGE, 1, true, 999999.99, '+0 month', 'Y-m-d'],
            [OrderPaymentType::CASH, OperationType::REPORT, 1, false, 999999.99, '+0 month', 'Y-m-d'],
            [OrderPaymentType::CASH, OperationType::RENT, 3, true, 333333.33, '+1 month', 'Y-m-t'] // has +-1 day
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param string $shiftedOn
     * @param string $formatDate
     *
     * @test
     * @dataProvider shouldShiftContractPaidToDateDataProvider
     * Should work with any payment types of order
     * Should shift paid_to of contract calculated only use rent operations amount on count days
     * (if sum = rent should shift on 1 month)
     */
    public function setCompleteShouldShiftContractPaidToDate(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $shiftedOn,
        $formatDate
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );
        $paidTo = clone $order->getContract()->getPaidTo();

        $statusManager->setComplete($order);

        $this->assertEquals(
            $paidTo->modify($shiftedOn)->format($formatDate),
            $order->getContract()->getPaidTo()->format($formatDate),
            sprintf('PaidTo date of contract should be shifted on %s', $shiftedOn)
        );
    }

    /**
     * @return array
     */
    public function shouldShiftPaymentPaidForDateDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT, 1, PaymentStatus::ACTIVE, PaymentType::ONE_TIME, '+1 month'],
            [OrderPaymentType::BANK, OperationType::RENT, 2, PaymentStatus::ACTIVE, PaymentType::RECURRING, '+2 month'],
            [OrderPaymentType::CASH, OperationType::RENT, 3, PaymentStatus::ACTIVE, PaymentType::RECURRING, '+3 month'],
            [OrderPaymentType::BANK, OperationType::CHARGE, 1, PaymentStatus::ACTIVE, PaymentType::RECURRING, '0 days'],
            [OrderPaymentType::CARD, OperationType::REPORT, 1, PaymentStatus::ACTIVE, PaymentType::RECURRING, '0 days'],
            [OrderPaymentType::CASH, OperationType::REPORT, 1, PaymentStatus::CLOSE, PaymentType::RECURRING, '0 days'],
            [OrderPaymentType::CARD, OperationType::RENT, 1, PaymentStatus::CLOSE, PaymentType::RECURRING, '0 days'],
            [OrderPaymentType::BANK, OperationType::RENT, 1, PaymentStatus::CLOSE, PaymentType::ONE_TIME, '0 days'],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param string $paymentStatus one of PaymentStatus
     * @param string $paymentType one of PaymentType
     *
     * @param string $shiftedOn
     *
     * @test
     * @dataProvider shouldShiftPaymentPaidForDateDataProvider
     * Should work with any payment_types of order and any types of payment
     * Should shift paid_for of active payment only, calculated by count of rent operations only
     * (1 rent operations = 1 month)
     */
    public function setCompleteShouldShiftPaymentPaidForDate(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $paymentStatus,
        $paymentType,
        $shiftedOn
    ) {

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations
        );

        // Added payment
        $payment = new Payment();
        $payment->setStatus($paymentStatus);
        $payment->setType($paymentType);
        $payment->setTotal($order->getSum());
        $payment->setPaidFor(new DateTime());
        $payment->setStartDate();
        $payment->setPaymentAccount($order->getContract()->getTenant()->getPaymentAccounts()->first());
        $payment->setContract($order->getContract());
        $order->getContract()->addPayment($payment);

        $paidFor = clone $payment->getPaidFor();

        $statusManager->setComplete($order);

        $this->assertEquals(
            $paidFor->modify($shiftedOn),
            $order->getContract()->getPayments()->last()->getPaidFor(),
            sprintf('PaidFor date of %s payment should be shifted on %s', $paymentStatus, $shiftedOn)
        );
    }

    /**
     * @return array
     */
    public function shouldSendRentReceiptEmailDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT, 1, false],
            [OrderPaymentType::BANK, OperationType::RENT, 2, true],
            [OrderPaymentType::BANK, OperationType::CHARGE, 1, true],
            [OrderPaymentType::CARD, OperationType::REPORT, 1, true],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     *
     * @test
     * @dataProvider shouldSendRentReceiptEmailDataProvider
     * Should send rent receipt email
     * if we have last operation rent or other type with card or bank payment type of order
     */
    public function setCompleteShouldSendRentEmails(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation
    ) {

        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->once())
            ->method('sendRentReceipt');
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );
        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation
        );

        $statusManager->setComplete($order);
    }

    /**
     * @return array
     */
    public function shouldSendReportReceiptEmailDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::REPORT],
            [OrderPaymentType::BANK, OperationType::REPORT],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     *
     * @test
     * @dataProvider shouldSendReportReceiptEmailDataProvider
     * Should send report receipt email
     * if we have last operation report type with card or bank payment type of order
     */
    public function setCompleteShouldSendReportEmails($orderPaymentType, $mainOperationsType)
    {

        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->once())
            ->method('sendReportReceipt');
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );
        $order = $this->createOrder($orderPaymentType, $mainOperationsType);

        $statusManager->setComplete($order);
    }

    /**
     * @return array
     */
    public function shouldNotSendRentReceiptEmailDataProvider()
    {
        return [
            [OrderPaymentType::CASH, OperationType::RENT],
            [OrderPaymentType::CASH, OperationType::OTHER],
            [OrderPaymentType::CASH, OperationType::CHARGE],
            [OrderPaymentType::CASH, OperationType::REPORT],
            [OrderPaymentType::BANK, OperationType::CHARGE],
            [OrderPaymentType::CARD, OperationType::CHARGE],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     *
     * @test
     * @dataProvider shouldNotSendRentReceiptEmailDataProvider
     * Should not send any emails if payment type of order is cash
     * Should not send any emails if we have last operation is not rent, other or report type
     *  with any payment type of order expect for cash
     */
    public function setCompleteShouldNotSendAnyEmails($orderPaymentType, $mainOperationsType)
    {

        $mailerMock = $this->getMailerMock(true);
        $mailerMock
            ->expects($this->never())
            ->method('sendBaseLetter');

        $mailerMock
            ->expects($this->never())
            ->method('sendEmail');

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );

        $order = $this->createOrder($orderPaymentType, $mainOperationsType);

        $statusManager->setComplete($order);
    }

    /**
     * @test
     */
    public function setCancelledShouldSetStatusCancelled()
    {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder();

        $this->assertNotEquals(
            OrderStatus::CANCELLED,
            $order->getStatus(),
            'Order should not have status "cancelled"'
        );

        $statusManager->setCancelled($order);

        $this->assertEquals(
            OrderStatus::CANCELLED,
            $order->getStatus(),
            'Order should be updated to "cancelled" status'
        );
    }

    /**
     * @return array
     */
    public function shouldUnshiftContractPaidToDateDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT, 1, false, 999999.99, '-1 month', 'Y-m-d'],
            [OrderPaymentType::BANK, OperationType::RENT, 2, true, 999999.99, '-2 month', 'Y-m-d'],
            [OrderPaymentType::BANK, OperationType::CHARGE, 1, true, 999999.99, '+0 month', 'Y-m-d'],
            [OrderPaymentType::CASH, OperationType::REPORT, 1, false, 999999.99, '+0 month', 'Y-m-d'],
            [OrderPaymentType::CASH, OperationType::RENT, 3, true, 333333.33, '-1 month', 'Y-m-t'] // has +-1 day
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param string $shiftedOn
     * @param string $formatDate
     *
     * @test
     * @dataProvider shouldUnshiftContractPaidToDateDataProvider
     * Should work with any payment types of order
     * Should unshift paid_to of contract calculated only use rent operations amount on count days
     * (if sum = rent should unshift on 1 month)
     */
    public function setCancelledShouldUnshiftContractPaidToDate(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $shiftedOn,
        $formatDate
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );
        $paidTo = clone $order->getContract()->getPaidTo();

        $statusManager->setCancelled($order);

        $this->assertEquals(
            $paidTo->modify($shiftedOn)->format($formatDate),
            $order->getContract()->getPaidTo()->format($formatDate),
            sprintf('PaidTo date of contract should be unshifted on %s', $shiftedOn)
        );
    }

    /**
     * @return array
     */
    public function shouldUnshiftPaymentPaidForDateDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT, 1, PaymentStatus::ACTIVE, PaymentType::ONE_TIME, '-1 month'],
            [OrderPaymentType::BANK, OperationType::RENT, 2, PaymentStatus::ACTIVE, PaymentType::RECURRING, '-2 month'],
            [OrderPaymentType::CASH, OperationType::RENT, 3, PaymentStatus::ACTIVE, PaymentType::RECURRING, '-3 month'],
            [OrderPaymentType::BANK, OperationType::CHARGE, 1, PaymentStatus::ACTIVE, PaymentType::RECURRING, '0 days'],
            [OrderPaymentType::CARD, OperationType::REPORT, 1, PaymentStatus::ACTIVE, PaymentType::RECURRING, '0 days'],
            [OrderPaymentType::CASH, OperationType::REPORT, 1, PaymentStatus::CLOSE, PaymentType::RECURRING, '0 days'],
            [OrderPaymentType::CARD, OperationType::RENT, 1, PaymentStatus::CLOSE, PaymentType::RECURRING, '0 days'],
            [OrderPaymentType::BANK, OperationType::RENT, 1, PaymentStatus::CLOSE, PaymentType::ONE_TIME, '0 days'],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param string $paymentStatus one of PaymentStatus
     * @param string $paymentType one of PaymentType
     *
     * @param string $shiftedOn
     *
     * @test
     * @dataProvider shouldUnshiftPaymentPaidForDateDataProvider
     * Should work with any payment_types of order and any types of payment
     * Should unshift paid_for of active payment only, calculated by count of rent operations only
     * (1 rent operations = 1 month)
     */
    public function setCancelledShouldShiftPaymentPaidForDate(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $paymentStatus,
        $paymentType,
        $shiftedOn
    ) {

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations
        );

        // Added payment
        $payment = new Payment();
        $payment->setStatus($paymentStatus);
        $payment->setType($paymentType);
        $payment->setTotal($order->getSum());
        $payment->setPaidFor(new DateTime());
        $payment->setStartDate();
        $payment->setPaymentAccount($order->getContract()->getTenant()->getPaymentAccounts()->first());
        $payment->setContract($order->getContract());
        $order->getContract()->addPayment($payment);

        $paidFor = clone $payment->getPaidFor();

        $statusManager->setCancelled($order);

        $this->assertEquals(
            $paidFor->modify($shiftedOn),
            $order->getContract()->getPayments()->last()->getPaidFor(),
            sprintf('PaidFor date of %s payment should be unshifted on %s', $paymentStatus, $shiftedOn)
        );
    }

    /**
     * @return array
     */
    public function shouldSendReversalEmailDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT, 1, false],
            [OrderPaymentType::BANK, OperationType::RENT, 2, true],
            [OrderPaymentType::BANK, OperationType::CHARGE, 1, true],
            [OrderPaymentType::CARD, OperationType::REPORT, 1, true],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     *
     * @test
     * @dataProvider shouldSendReversalEmailDataProvider
     * Should send reversal emails
     * if we have last operation rent or other type with card or bank payment type of order
     */
    public function setCancelledShouldSendEmails(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation
    ) {

        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->once())
            ->method('sendOrderCancelToTenant');
        $mailerMock->expects($this->once())
            ->method('sendOrderCancelToLandlord');
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );
        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation
        );

        $statusManager->setCancelled($order);
    }

    /**
     * @return array
     */
    public function shouldNotSendReversalEmailDataProvider()
    {
        return [
            [OrderPaymentType::CASH, OperationType::RENT],
            [OrderPaymentType::CASH, OperationType::OTHER],
            [OrderPaymentType::CASH, OperationType::CHARGE],
            [OrderPaymentType::CASH, OperationType::REPORT],
            [OrderPaymentType::BANK, OperationType::CHARGE],
            [OrderPaymentType::CARD, OperationType::CHARGE],
            [OrderPaymentType::BANK, OperationType::REPORT],
            [OrderPaymentType::CARD, OperationType::REPORT],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     *
     * @test
     * @dataProvider shouldNotSendReversalEmailDataProvider
     * Should not send any emails if payment type of order is cash
     * Should not send any emails if we have last operation is not rent or other type
     *  with any payment type of order expect for cash
     */
    public function setCancelledShouldNotSendEmails($orderPaymentType, $mainOperationsType)
    {

        $mailerMock = $this->getMailerMock(true);
        $mailerMock
            ->expects($this->never())
            ->method('sendBaseLetter');

        $mailerMock
            ->expects($this->never())
            ->method('sendEmail');

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );

        $order = $this->createOrder($orderPaymentType, $mainOperationsType);

        $statusManager->setCancelled($order);
    }

    /**
     * @test
     */
    public function setRefundedShouldSetStatusRefunded()
    {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );
        // create card order with 1 rent operations
        $order = $this->createOrder();

        $this->assertNotEquals(
            OrderStatus::REFUNDED,
            $order->getStatus(),
            'Order should not have status "refunded"'
        );

        $statusManager->setRefunded($order);

        $this->assertEquals(
            OrderStatus::REFUNDED,
            $order->getStatus(),
            'Order should be updated to "refunded" status'
        );
    }

    /**
     * @return array
     */
    public function revertBalanceForContractDataProvider()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT, 2, true, 500, 10999.89, 1100],
            [OrderPaymentType::CARD, OperationType::RENT, 0, true, 500, 9999.89, 100],
            [OrderPaymentType::CARD, OperationType::REPORT, 1, false, 500, 9999.89, 0],
            [OrderPaymentType::CASH, OperationType::CHARGE, 1, true, 500, 9999.89, 100],
            [OrderPaymentType::BANK, OperationType::RENT, 1, true, 500, 10499.89, 600],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param float $resultBalance
     * @param float $resultIntegratedBalance
     *
     * @test
     * @dataProvider revertBalanceForContractDataProvider
     * Should work with any payment types of order
     * Should revert minus sum of all others and rent operations from integratedBalance
     * and from balance only rent operations
     */
    public function setRefundedShouldRevertBalanceForContractIntegratedGroup(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $resultBalance,
        $resultIntegratedBalance
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );

        $this->assertTrue(
            $order->getContract()->getGroupSettings()->getIsIntegrated(),
            'Group is not integrated, please check fixtures'
        );
        $this->assertEquals(
            9999.89,
            $order->getContract()->getBalance(),
            sprintf(
                'Contract with id #%s has incorrect balance, please check fixtures',
                $order->getContract()->getId()
            )
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            sprintf(
                'Contract with id #%s has incorrect integrated balance, please check fixtures',
                $order->getContract()->getId()
            )
        );

        $statusManager->setRefunded($order);

        $this->assertEquals(
            $resultBalance,
            $order->getContract()->getBalance(),
            'Contract for integration group should revert balance whole sum of all only rent operations'
        );
        $this->assertEquals(
            $resultIntegratedBalance,
            $order->getContract()->getIntegratedBalance(),
            'Contract for integration group should revert integratedBalance whole sum of all rent operations' .
            ' and all other operations'
        );
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param float $resultBalance
     *
     * @test
     * @dataProvider revertBalanceForContractDataProvider
     * Should work with any payment types of order
     * Should revert minus sum of all only rent operations from balance
     *  and integratedBalance shouldn't be changed
     */
    public function setRefundedShouldRevertBalanceForContractNotIntegratedGroup(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $resultBalance
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );

        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(25);
        $this->assertNotNull($group, 'Group with id 25 can\'t be found, please check fixtures');
        $this->assertFalse(
            $group->getGroupSettings()->getIsIntegrated(),
            'Group is integrated, please check fixtures'
        );
        $order->getContract()->setGroup($group);
        $this->getEntityManager()->persist($order->getContract());

        $this->assertEquals(
            9999.89,
            $order->getContract()->getBalance(),
            sprintf(
                'Contract with id #%s has incorrect balance, please check fixtures',
                $order->getContract()->getId()
            )
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            sprintf(
                'Contract with id #%s has incorrect integrated balance, please check fixtures',
                $order->getContract()->getId()
            )
        );

        $statusManager->setRefunded($order);

        $this->assertEquals(
            $resultBalance,
            $order->getContract()->getBalance(),
            'Contract for integration group should revert balance whole sum of all only rent operations'
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            'IntegratedBalance on contract for not integration group shouldn\'t be reverted'
        );
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param string $shiftedOn
     * @param string $formatDate
     *
     * @test
     * @dataProvider shouldUnshiftContractPaidToDateDataProvider
     * Should work with any payment types of order
     * Should unshift paid_to of contract calculated only use rent operations amount on count days
     * (if sum = rent should unshift on 1 month)
     */
    public function setRefundedShouldUnshiftContractPaidToDate(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $shiftedOn,
        $formatDate
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );
        $paidTo = clone $order->getContract()->getPaidTo();

        $statusManager->setRefunded($order);

        $this->assertEquals(
            $paidTo->modify($shiftedOn)->format($formatDate),
            $order->getContract()->getPaidTo()->format($formatDate),
            sprintf('PaidTo date of contract should be unshifted on %s', $shiftedOn)
        );
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param string $paymentStatus one of PaymentStatus
     * @param string $paymentType one of PaymentType
     *
     * @param string $shiftedOn
     *
     * @test
     * @dataProvider shouldUnshiftPaymentPaidForDateDataProvider
     * Should work with any payment_types of order and any types of payment
     * Should unshift paid_for of active payment only, calculated by count of rent operations only
     * (1 rent operations = 1 month)
     */
    public function setRefundedShouldShiftPaymentPaidForDate(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $paymentStatus,
        $paymentType,
        $shiftedOn
    ) {

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations
        );

        // Added payment
        $payment = new Payment();
        $payment->setStatus($paymentStatus);
        $payment->setType($paymentType);
        $payment->setTotal($order->getSum());
        $payment->setPaidFor(new DateTime());
        $payment->setStartDate();
        $payment->setPaymentAccount($order->getContract()->getTenant()->getPaymentAccounts()->first());
        $payment->setContract($order->getContract());
        $order->getContract()->addPayment($payment);

        $paidFor = clone $payment->getPaidFor();

        $statusManager->setRefunded($order);

        $this->assertEquals(
            $paidFor->modify($shiftedOn),
            $order->getContract()->getPayments()->last()->getPaidFor(),
            sprintf('PaidFor date of %s payment should be unshifted on %s', $paymentStatus, $shiftedOn)
        );
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     *
     * @test
     * @dataProvider shouldSendReversalEmailDataProvider
     * Should send reversal emails
     * if we have last operation rent or other type with card or bank payment type of order
     */
    public function setRefundedShouldSendEmails(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation
    ) {

        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->once())
            ->method('sendOrderCancelToTenant');
        $mailerMock->expects($this->once())
            ->method('sendOrderCancelToLandlord');
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );
        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation
        );

        $statusManager->setRefunded($order);
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     *
     * @test
     * @dataProvider shouldNotSendReversalEmailDataProvider
     * Should not send any emails if payment type of order is cash
     * Should not send any emails if we have last operation is not rent or other type
     *  with any payment type of order expect for cash
     */
    public function setRefundedShouldNotSendEmails($orderPaymentType, $mainOperationsType)
    {

        $mailerMock = $this->getMailerMock(true);
        $mailerMock
            ->expects($this->never())
            ->method('sendBaseLetter');

        $mailerMock
            ->expects($this->never())
            ->method('sendEmail');

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );

        $order = $this->createOrder($orderPaymentType, $mainOperationsType);

        $statusManager->setRefunded($order);
    }

    /**
     * @test
     */
    public function setReturnedShouldSetStatusReturned()
    {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );
        // create card order with 1 rent operations
        $order = $this->createOrder();

        $this->assertNotEquals(
            OrderStatus::RETURNED,
            $order->getStatus(),
            'Order should not have status "returned"'
        );

        $statusManager->setReturned($order);

        $this->assertEquals(
            OrderStatus::RETURNED,
            $order->getStatus(),
            'Order should be updated to "returned" status'
        );
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param float $resultBalance
     * @param float $resultIntegratedBalance
     *
     * @test
     * @dataProvider revertBalanceForContractDataProvider
     * Should work with any payment types of order
     * Should revert minus sum of all others and rent operations from integratedBalance
     * and from balance only rent operations
     */
    public function setReturnedShouldRevertBalanceForContractIntegratedGroup(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $resultBalance,
        $resultIntegratedBalance
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );

        $this->assertTrue(
            $order->getContract()->getGroupSettings()->getIsIntegrated(),
            'Group is not integrated, please check fixtures'
        );
        $this->assertEquals(
            9999.89,
            $order->getContract()->getBalance(),
            sprintf(
                'Contract with id #%s has incorrect balance, please check fixtures',
                $order->getContract()->getId()
            )
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            sprintf(
                'Contract with id #%s has incorrect integrated balance, please check fixtures',
                $order->getContract()->getId()
            )
        );

        $statusManager->setReturned($order);

        $this->assertEquals(
            $resultBalance,
            $order->getContract()->getBalance(),
            'Contract for integration group should revert balance whole sum of all only rent operations'
        );
        $this->assertEquals(
            $resultIntegratedBalance,
            $order->getContract()->getIntegratedBalance(),
            'Contract for integration group should revert integratedBalance whole sum of all rent operations' .
            ' and all other operations'
        );
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     * @param float $amountForMainOperation
     *
     * @param float $resultBalance
     *
     * @test
     * @dataProvider revertBalanceForContractDataProvider
     * Should work with any payment types of order
     * Should revert minus sum of all only rent operations from balance
     *  and integratedBalance shouldn't be changed
     */
    public function setReturnedShouldRevertBalanceForContractNotIntegratedGroup(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation,
        $amountForMainOperation,
        $resultBalance
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation,
            $amountForMainOperation
        );

        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(25);
        $this->assertNotNull($group, 'Group with id 25 can\'t be found, please check fixtures');
        $this->assertFalse(
            $group->getGroupSettings()->getIsIntegrated(),
            'Group is integrated, please check fixtures'
        );
        $order->getContract()->setGroup($group);
        $this->getEntityManager()->persist($order->getContract());

        $this->assertEquals(
            9999.89,
            $order->getContract()->getBalance(),
            sprintf(
                'Contract with id #%s has incorrect balance, please check fixtures',
                $order->getContract()->getId()
            )
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            sprintf(
                'Contract with id #%s has incorrect integrated balance, please check fixtures',
                $order->getContract()->getId()
            )
        );

        $statusManager->setReturned($order);

        $this->assertEquals(
            $resultBalance,
            $order->getContract()->getBalance(),
            'Contract for integration group should revert balance whole sum of all only rent operations'
        );
        $this->assertEquals(
            0,
            $order->getContract()->getIntegratedBalance(),
            'IntegratedBalance on contract for not integration group shouldn\'t be reverted'
        );
    }

    /**
     * @return array
     */
    public function shouldCloseACHRecurringPaymentDataProvider()
    {
        return [
            [OrderPaymentType::BANK, PaymentStatus::ACTIVE, PaymentType::RECURRING, PaymentStatus::CLOSE],
            [OrderPaymentType::CARD, PaymentStatus::ACTIVE, PaymentType::RECURRING, PaymentStatus::ACTIVE],
            [OrderPaymentType::CASH, PaymentStatus::ACTIVE, PaymentType::RECURRING, PaymentStatus::ACTIVE],
            [OrderPaymentType::BANK, PaymentStatus::ACTIVE, PaymentType::ONE_TIME, PaymentStatus::ACTIVE],
            [OrderPaymentType::CARD, PaymentStatus::ACTIVE, PaymentType::ONE_TIME, PaymentStatus::ACTIVE],
            [OrderPaymentType::CASH, PaymentStatus::ACTIVE, PaymentType::ONE_TIME, PaymentStatus::ACTIVE],
            [OrderPaymentType::BANK, PaymentStatus::CLOSE, PaymentType::RECURRING, PaymentStatus::CLOSE],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $paymentStatus one of PaymentStatus
     * @param string $paymentType one of PaymentType
     *
     * @param string $resultStatus one of PaymentStatus
     * @test
     * @dataProvider shouldCloseACHRecurringPaymentDataProvider
     */
    public function setReturnedShouldCloseACHRecurringPayment(
        $orderPaymentType,
        $paymentStatus,
        $paymentType,
        $resultStatus
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder($orderPaymentType);

        $this->assertCount(0, $order->getContract()->getPayments(), 'Should create order without payments');

        // Added payment
        $payment = new Payment();
        $payment->setStatus($paymentStatus);
        $payment->setType($paymentType);
        $payment->setTotal($order->getSum());
        $payment->setPaidFor(new DateTime());
        $payment->setStartDate();
        $payment->setPaymentAccount($order->getContract()->getTenant()->getPaymentAccounts()->first());
        $payment->setContract($order->getContract());
        $order->getContract()->addPayment($payment);

        $statusManager->setReturned($order);

        $this->assertCount(1, $order->getContract()->getPayments(), 'Payments wasn\'t added');

        $this->assertEquals(
            $resultStatus,
            $payment->getStatus(),
            'Should close active recurring payment if order payment type is "bank", others should be ignored'
        );
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     * @param int $countOperations
     * @param bool $isNeedCreateOtherOperation
     *
     * @test
     * @dataProvider shouldSendReversalEmailDataProvider
     * Should send reversal emails
     * if we have last operation rent or other type with card or bank payment type of order
     */
    public function setReturnedShouldSendEmails(
        $orderPaymentType,
        $mainOperationsType,
        $countOperations,
        $isNeedCreateOtherOperation
    ) {

        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->once())
            ->method('sendOrderCancelToTenant');
        $mailerMock->expects($this->once())
            ->method('sendOrderCancelToLandlord');
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );
        $order = $this->createOrder(
            $orderPaymentType,
            $mainOperationsType,
            $countOperations,
            $isNeedCreateOtherOperation
        );

        $statusManager->setReturned($order);
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $mainOperationsType one of OperationType
     *
     * @test
     * @dataProvider shouldNotSendReversalEmailDataProvider
     * Should not send any emails if payment type of order is cash
     * Should not send any emails if we have last operation is not rent or other type
     *  with any payment type of order expect for cash
     */
    public function setReturnedShouldNotSendEmails($orderPaymentType, $mainOperationsType)
    {

        $mailerMock = $this->getMailerMock(true);
        $mailerMock
            ->expects($this->never())
            ->method('sendBaseLetter');

        $mailerMock
            ->expects($this->never())
            ->method('sendEmail');

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );

        $order = $this->createOrder($orderPaymentType, $mainOperationsType);

        $statusManager->setReturned($order);
    }

    /**
     * @return array
     */
    public function shouldSetStatusPendingDataProvider()
    {
        return [
            [OrderPaymentType::BANK, PaymentProcessor::ACI, OrderStatus::PENDING],
            [OrderPaymentType::CARD, PaymentProcessor::ACI, OrderStatus::PENDING],
            [OrderPaymentType::CASH, PaymentProcessor::ACI, OrderStatus::PENDING],
            [OrderPaymentType::BANK, PaymentProcessor::HEARTLAND, OrderStatus::PENDING],
            [OrderPaymentType::CASH, PaymentProcessor::HEARTLAND, OrderStatus::PENDING],
            [OrderPaymentType::CARD, PaymentProcessor::HEARTLAND, OrderStatus::COMPLETE],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $paymentProcessor one of PaymentProcessor
     *
     * @param string $resultOrderStatus
     *
     * Should set "pending" status for any orders expect with payment_processor "heartland" and payment_type "card"
     * @test
     * @dataProvider shouldSetStatusPendingDataProvider
     */
    public function setPendingShouldSetStatusPending(
        $orderPaymentType,
        $paymentProcessor,
        $resultOrderStatus
    ) {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $order = $this->createOrder($orderPaymentType);
        $order->setPaymentProcessor($paymentProcessor);

        $this->assertNotEquals(OrderStatus::PENDING, $order->getStatus());
        $this->assertNotEquals(OrderStatus::COMPLETE, $order->getStatus());

        $statusManager->setPending($order);

        $this->assertEquals(
            $resultOrderStatus,
            $order->getStatus(),
            sprintf('Order should be updated to "%s" status', $resultOrderStatus)
        );
    }

    /**
     * Should call method setComplete for order with payment_processor "heartland" and payment_type "card"
     * @test
     */
    public function setPendingShouldCallMethodSetComplete()
    {
        $order = $this->createOrder(OrderPaymentType::CARD);
        $order->setPaymentProcessor(PaymentProcessor::HEARTLAND);

        /** @var OrderSubmerchantStatusManager|\PHPUnit_Framework_MockObject_MockObject $statusManager */
        $statusManager = $this->getMock(
            '\RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderSubmerchantStatusManager',
            ['setComplete'],
            [$this->getEntityManager(), $this->getLoggerMock(), $this->getMailerMock()]
        );

        $statusManager->expects($this->once())
            ->method('setComplete');

        $statusManager->setPending($order);

        $order->setPaymentProcessor(PaymentProcessor::ACI);

        $statusManager = $this->getMock(
            '\RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderSubmerchantStatusManager',
            ['setComplete', 'updateStatus'],
            [],
            '',
            false
        );

        $statusManager->expects($this->never())
            ->method('setComplete');

        $statusManager->expects($this->once())
            ->method('updateStatus');

        $statusManager->setPending($order);
    }

    /**
     * @return array
     */
    public function shouldSendPendingInfoEmailDataProviderACI()
    {
        return [
            [OrderPaymentType::BANK, OperationType::RENT],
            [OrderPaymentType::BANK, OperationType::REPORT],
            [OrderPaymentType::BANK, OperationType::OTHER],
            [OrderPaymentType::BANK, OperationType::CHARGE],
            [OrderPaymentType::CASH, OperationType::RENT],
            [OrderPaymentType::CASH, OperationType::OTHER],
            [OrderPaymentType::CASH, OperationType::CHARGE],
            [OrderPaymentType::CASH, OperationType::REPORT],
            [OrderPaymentType::CARD, OperationType::RENT],
            [OrderPaymentType::CARD, OperationType::OTHER],
            [OrderPaymentType::CARD, OperationType::CHARGE],
            [OrderPaymentType::CARD, OperationType::REPORT],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $operationType one of OperationType
     *
     * @test
     * @dataProvider shouldSendPendingInfoEmailDataProviderACI
     * Should send pending info email for any type of order and operation for ACI PaymentProcessor
     */
    public function setPendingShouldSendEmailsACI($orderPaymentType, $operationType)
    {
        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->once())
            ->method('sendPendingInfo');

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );

        $order = $this->createOrder($orderPaymentType, $operationType);
        $order->setPaymentProcessor(PaymentProcessor::ACI);

        $statusManager->setPending($order);
    }

    /**
     * @return array
     */
    public function shouldSendPendingInfoEmailDataProviderHEARTLAND()
    {
        return [
            [OrderPaymentType::BANK, OperationType::RENT],
            [OrderPaymentType::BANK, OperationType::REPORT],
            [OrderPaymentType::BANK, OperationType::OTHER],
            [OrderPaymentType::BANK, OperationType::CHARGE],
            [OrderPaymentType::CASH, OperationType::RENT],
            [OrderPaymentType::CASH, OperationType::OTHER],
            [OrderPaymentType::CASH, OperationType::CHARGE],
            [OrderPaymentType::CASH, OperationType::REPORT],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $operationType one of OperationType
     *
     * @test
     * @dataProvider shouldSendPendingInfoEmailDataProviderHEARTLAND
     * Should send pending info email for any type of operation and payment type of order expect card
     *  for HEARTLAND PaymentProcessor
     */
    public function setPendingShouldSendEmailsHEARTLAND($orderPaymentType, $operationType)
    {
        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->once())
            ->method('sendPendingInfo');

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );

        $order = $this->createOrder($orderPaymentType, $operationType);
        $order->setPaymentProcessor(PaymentProcessor::HEARTLAND);

        $statusManager->setPending($order);
    }

    /**
     * @return array
     */
    public function shouldNotSendPendingInfoEmailDataProviderHEARTLAND()
    {
        return [
            [OrderPaymentType::CARD, OperationType::RENT],
            [OrderPaymentType::CARD, OperationType::OTHER],
            [OrderPaymentType::CARD, OperationType::CHARGE],
            [OrderPaymentType::CARD, OperationType::REPORT],
        ];
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $operationType one of OperationType
     *
     * @test
     * @dataProvider shouldNotSendPendingInfoEmailDataProviderHEARTLAND
     * Should not send pending info email for any type of operation if order payment type "card"
     *  for HEARTLAND PaymentProcessor
     */
    public function setPendingShouldNotSendEmailsHEARTLAND($orderPaymentType, $operationType)
    {
        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->never())
            ->method('sendPendingInfo');

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );

        $order = $this->createOrder($orderPaymentType, $operationType);
        $order->setPaymentProcessor(PaymentProcessor::HEARTLAND);

        $statusManager->setPending($order);
    }

    /**
     * @test
     */
    public function setErrorShouldSetStatusError()
    {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );
        // create card order with 1 rent operations
        $order = $this->createOrder();

        $this->assertNotEquals(
            OrderStatus::ERROR,
            $order->getStatus(),
            'Order should not have status "error"'
        );

        $statusManager->setError($order);

        $this->assertEquals(
            OrderStatus::ERROR,
            $order->getStatus(),
            'Order should be updated to "error" status'
        );
    }

    /**
     * @return array
     */
    public function shouldSendErrorEmailDataProvider()
    {
        return $this->shouldSendPendingInfoEmailDataProviderACI();
    }

    /**
     * @param string $orderPaymentType one of OrderPaymentType
     * @param string $operationType one of OperationType
     *
     * @test
     * @dataProvider shouldSendErrorEmailDataProvider
     * Should send rent error email for any orders and operations
     */
    public function setErrorShouldSendEmails($orderPaymentType, $operationType)
    {
        $mailerMock = $this->getMailerMock();
        $mailerMock->expects($this->once())
            ->method('sendRentError');
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $mailerMock
        );

        $order = $this->createOrder($orderPaymentType, $operationType);

        $statusManager->setError($order);
    }

    /**
     * @test
     */
    public function setNewShouldSetStatusNew()
    {
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );
        // create card order with 1 rent operations
        $order = $this->createOrder();

        $this->assertNotEquals(
            OrderStatus::NEWONE,
            $order->getStatus(),
            'Order should not have status "new"'
        );

        $statusManager->setNew($order);

        $this->assertEquals(
            OrderStatus::NEWONE,
            $order->getStatus(),
            'Order should be updated to "new" status'
        );
    }

    /**
     * @test
     */
    public function setNewShouldSetChargePartner()
    {
        $this->load(true);

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );
        // create card order with 1 rent operations
        $order = $this->createOrder();
        /** @var Tenant $user */
        $user = $this->getEntityManager()
            ->getRepository('DataBundle:User')
            ->findOneBy(['email' => 'robert@rentrack.com']);
        $this->assertNotNull($user);
        $this->assertNotNull($user->getPartnerCode());
        $this->assertNull($user->getPartnerCode()->getFirstPaymentDate());
        $order->getContract()->setTenant($user);
        $order->setUser($user);

        $statusManager->setNew($order);

        $this->getEntityManager()->refresh($user->getPartnerCode());

        $date = new DateTime();
        $this->assertEquals($date->format('Y-m-d'), $user->getPartnerCode()->getFirstPaymentDate()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function setNewShouldUpdateStartAtOfContract()
    {
        $this->load(true);

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        $contract = $this->getContract($startAt, $finishAt);
        $this->assertTrue(($contract->getOperations()->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));

        $order = $this->createOrder(OrderPaymentType::CARD, OperationType::RENT);

        $order->getOperations()->first()->setContract($contract);

        $statusManager->setNew($order);

        $this->getEntityManager()->refresh($contract);

        $this->assertEquals((new DateTime())->format('Y-m-d'), $contract->getStartAt()->format('Y-m-d'));

        $contract->setStartAt($startAt);

        $this->getEntityManager()->flush($contract);

        return [$contract->getId(), $startAt];
    }

    /**
     * @param array $params
     *
     * @test
     * @depends setNewShouldUpdateStartAtOfContract
     */
    public function setNewShouldNotUpdateStartAtOfContractNextTime(array $params)
    {
        /** @var DateTime $startAt */
        list ($contractId, $startAt) = $params;
        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find($contractId);

        $this->assertNotNull($contract, 'Should create contract before');

        $order = $this->createOrder(OrderPaymentType::BANK, OperationType::RENT);

        $order->getOperations()->first()->setContract($contract);

        $statusManager->setNew($order);

        $this->getEntityManager()->refresh($contract);

        $this->assertEquals($startAt->format('Y-m-d'), $contract->getStartAt()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function setNewShouldNotUpdateStartAtOfContractWithoutRentOperations()
    {
        $this->load(true);

        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');

        $contract = $this->getContract($startAt, $finishAt);
        $this->assertTrue(($contract->getOperations()->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));

        $order = $this->createOrder(OrderPaymentType::BANK, OperationType::OTHER);

        $order->getOperations()->first()->setContract($contract);

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $statusManager->setNew($order);

        $this->getEntityManager()->refresh($contract);

        $this->assertEquals($startAt->format('Y-m-d'), $contract->getStartAt()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function setNewShouldUpdateStartAtOfContractUseEarliestOperation()
    {
        $this->load(true);

        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');

        $contract = $this->getContract($startAt, $finishAt);
        $this->assertTrue(($contract->getOperations()->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));

        $order = $this->createOrder(OrderPaymentType::CASH, OperationType::RENT, 3);

        /** @var Operation[]|\Doctrine\Common\Collections\Collection $operations */
        $operations = $order->getOperations();
        $this->assertCount(3, $operations);

        $operations[0]->setContract($contract);
        $operations[1]->setContract($contract);
        $operations[2]->setContract($contract);

        $operations[0]->setPaidFor((new DateTime()));
        $operations[1]->setPaidFor((new DateTime())->modify('-1 month')); // earliest operation
        $operations[2]->setPaidFor((new DateTime())->modify('+1 month'));

        $statusManager = new OrderSubmerchantStatusManager(
            $this->getEntityManager(),
            $this->getLoggerMock(),
            $this->getMailerMock()
        );

        $statusManager->setNew($order);

        $this->getEntityManager()->refresh($contract);
        $this->getEntityManager()->refresh($operations[1]);

        $this->assertEquals($operations[1]->getPaidFor()->format('Y-m-d'), $contract->getStartAt()->format('Y-m-d'));
    }
}
