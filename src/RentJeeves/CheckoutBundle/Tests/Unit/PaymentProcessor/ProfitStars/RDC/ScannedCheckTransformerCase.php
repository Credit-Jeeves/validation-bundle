<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\ApiBundle\Services\Encoders\Skip32IdEncoder;
use RentJeeves\ApiBundle\Services\Encoders\ValidationEncoderException;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ScannedCheckTransformer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\ProfitStarsTransaction;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSItemStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositItem;

class ScannedCheckTransformerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException
     * @expectedExceptionMessage Customer number test is invalid, can not skip32 decode.
     */
    public function shouldThrowExceptionWhenCustomerNumberCanNotBeDecoded()
    {
        $encoder = $this->getEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->with('test')
            ->will($this->throwException(new ValidationEncoderException()));

        $depositItem = new WSRemoteDepositItem();
        $depositItem->setCustomerNumber('test');

        $checkTransformer = new ScannedCheckTransformer(
            $encoder,
            $this->getContractRepositoryMock(),
            $this->getLoggerMock()
        );

        $checkTransformer->transformToOrder($depositItem);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException
     * @expectedExceptionMessage Contract not found for customerNumber "test" (contractId #18), batchNumber "b1"
     */
    public function shouldThrowExceptionIfContractNotFoundByCustomerNumber()
    {
        $encoder = $this->getEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->with('test')
            ->will($this->returnValue('18'));

        $repository = $this->getContractRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('find')
            ->with('18')
            ->will($this->returnValue(null));

        $depositItem = new WSRemoteDepositItem();
        $depositItem
            ->setCustomerNumber('test')
            ->setBatchNumber('b1');

        $checkTransformer = new ScannedCheckTransformer(
            $encoder,
            $repository,
            $this->getLoggerMock()
        );

        $checkTransformer->transformToOrder($depositItem);
    }

    /**
     * @test
     */
    public function shouldTransformDepositItemToPendingOrderIfItemHasCreatedStatus()
    {
        $encoder = $this->getEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->with('test')
            ->will($this->returnValue('18'));

        $contract = new Contract();
        $contract->setTenant(new Tenant());
        $contract->setGroup(new Group());

        $repository = $this->getContractRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('find')
            ->with('18')
            ->will($this->returnValue($contract));

        $depositItem = new WSRemoteDepositItem();
        $totalAmount = 999.99;
        $itemDateTime = (new \DateTime())->format('Y-m-d');
        $depositItem
            ->setItemId(125)
            ->setCustomerNumber('test')
            ->setBatchNumber('b1')
            ->setItemStatus(WSItemStatus::CREATED)
            ->setCheckNumber('0201')
            ->setItemDateTime($itemDateTime)
            ->setTotalAmount($totalAmount)
            ->setLocationId(102588)
            ->setReferenceNumber('ref-test');

        $checkTransformer = new ScannedCheckTransformer(
            $encoder,
            $repository,
            $this->getLoggerMock()
        );

        $order = $checkTransformer->transformToOrder($depositItem);
        $this->assertInstanceOf('CreditJeeves\DataBundle\Entity\Order', $order, 'Order entity is expected');
        /** @var Operation $operation */
        $this->assertInstanceOf(
            Operation::class,
            $operation = $order->getOperations()->first(),
            'Operation entity is expected'
        );
        $this->assertInstanceOf(
            Transaction::class,
            $transaction = $order->getCompleteTransaction(),
            'Transaction entity is expected'
        );
        $this->assertInstanceOf(
            ProfitStarsTransaction::class,
            $profitStarsTransaction = $order->getProfitStarsTransaction(),
            'ProfitStarsTransaction entity is expected'
        );
        $this->assertEquals(125, $profitStarsTransaction->getItemId(), 'ItemId should be set to 125');
        $this->assertEquals(OrderStatus::PENDING, $order->getStatus(), 'Order is expected to be PENDING');
        $this->assertEquals($totalAmount, $order->getSum(), sprintf('Order sum should be %s', $totalAmount));
        $this->assertEquals(
            PaymentProcessor::PROFIT_STARS,
            $order->getPaymentProcessor(),
            'Order should have PROFIT_STARS payment processor'
        );
        $this->assertEquals(
            OrderPaymentType::SCANNED_CHECK,
            $order->getPaymentType(),
            'Order should have SCANNED_CHECK type'
        );
        $this->assertEquals(
            '0201',
            $order->getCheckNumber(),
            'Order should have check number 0201'
        );
        $this->assertEquals(
            $itemDateTime,
            $order->getCreatedAt()->format('Y-m-d'),
            'Order should have expected createdAt'
        );
        $this->assertEquals(
            OperationType::RENT,
            $operation->getType(),
            'Operation should have type RENT'
        );
        $this->assertEquals(
            $totalAmount,
            $operation->getTotalAmount(),
            sprintf('Operation amount should be %s', $totalAmount)
        );
        $this->assertEquals(
            $itemDateTime,
            $operation->getCreatedAt()->format('Y-m-d'),
            'Operation should have expected createdAt'
        );
        $this->assertEquals(
            $itemDateTime,
            $operation->getPaidFor()->format('Y-m-d'),
            'Operation should have expected paidFor'
        );
        $this->assertEquals(
            $itemDateTime,
            $transaction->getCreatedAt()->format('Y-m-d'),
            'Transaction should have expected createdAt'
        );
        $this->assertEquals(
            $itemDateTime,
            $transaction->getBatchDate()->format('Y-m-d'),
            'Transaction should have expected batchDate'
        );
        $this->assertEquals(
            102588,
            $transaction->getMerchantName(),
            'Transaction should have merchantName 102588'
        );
        $this->assertEquals(
            'b1',
            $transaction->getBatchId(),
            'Transaction should have batchId b1'
        );
        $this->assertTrue($transaction->getIsSuccessful(), 'Transaction should be successful');
        $this->assertNull($transaction->getDepositDate(), 'Pending order can not have deposit date');
    }

    /**
     * @test
     */
    public function shouldTransformDepositItemToCompleteOrderIfItemHasSentToTransactionProcessingStatus()
    {
        $encoder = $this->getEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->with('test')
            ->will($this->returnValue('18'));

        $contract = new Contract();
        $contract->setTenant(new Tenant());
        $contract->setGroup(new Group());

        $repository = $this->getContractRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('find')
            ->with('18')
            ->will($this->returnValue($contract));

        $depositItem = new WSRemoteDepositItem();
        $totalAmount = 999.99;
        $itemDateTime = (new \DateTime())->format('Y-m-d');
        $depositItem
            ->setItemId('123')
            ->setCustomerNumber('test')
            ->setBatchNumber('b1')
            ->setItemStatus(WSItemStatus::SENTTOTRANSACTIONPROCESSING)
            ->setCheckNumber('0201')
            ->setItemDateTime($itemDateTime)
            ->setTotalAmount($totalAmount)
            ->setLocationId(102588)
            ->setReferenceNumber('ref-test');

        $checkTransformer = new ScannedCheckTransformer(
            $encoder,
            $repository,
            $this->getLoggerMock()
        );

        $order = $checkTransformer->transformToOrder($depositItem);
        $this->assertInstanceOf(Order::class, $order, 'Order entity is expected');
        /** @var Operation $operation */
        $this->assertInstanceOf(
            Operation::class,
            $operation = $order->getOperations()->first(),
            'Operation entity is expected'
        );
        $this->assertInstanceOf(
            Transaction::class,
            $transaction = $order->getCompleteTransaction(),
            'Transaction entity is expected'
        );
        $this->assertInstanceOf(
            ProfitStarsTransaction::class,
            $profitStarsTransaction = $order->getProfitStarsTransaction(),
            'ProfitStarsTransaction entity is expected'
        );
        $this->assertEquals(123, $profitStarsTransaction->getItemId(), 'ItemId should be set to 123');
        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus(), 'Order is expected to be COMPLETE');
        $this->assertEquals($totalAmount, $order->getSum(), sprintf('Order sum should be %s', $totalAmount));
        $this->assertEquals(
            PaymentProcessor::PROFIT_STARS,
            $order->getPaymentProcessor(),
            'Order should have PROFIT_STARS payment processor'
        );
        $this->assertEquals(
            OrderPaymentType::SCANNED_CHECK,
            $order->getPaymentType(),
            'Order should have SCANNED_CHECK type'
        );
        $this->assertEquals(
            '0201',
            $order->getCheckNumber(),
            'Order should have check number 0201'
        );
        $this->assertEquals(
            $itemDateTime,
            $order->getCreatedAt()->format('Y-m-d'),
            'Order should have expected createdAt'
        );
        $this->assertEquals(
            OperationType::RENT,
            $operation->getType(),
            'Operation should have type RENT'
        );
        $this->assertEquals(
            $totalAmount,
            $operation->getTotalAmount(),
            sprintf('Operation amount should be %s', $totalAmount)
        );
        $this->assertEquals(
            $itemDateTime,
            $operation->getCreatedAt()->format('Y-m-d'),
            'Operation should have expected createdAt'
        );
        $this->assertEquals(
            $itemDateTime,
            $operation->getPaidFor()->format('Y-m-d'),
            'Operation should have expected paidFor'
        );
        $this->assertEquals(
            $itemDateTime,
            $transaction->getCreatedAt()->format('Y-m-d'),
            'Transaction should have expected createdAt'
        );
        $this->assertEquals(
            $itemDateTime,
            $transaction->getBatchDate()->format('Y-m-d'),
            'Transaction should have expected batchDate'
        );
        $this->assertEquals(
            102588,
            $transaction->getMerchantName(),
            'Transaction should have merchantName 102588'
        );
        $this->assertEquals(
            'b1',
            $transaction->getBatchId(),
            'Transaction should have batchId b1'
        );
        $this->assertTrue($transaction->getIsSuccessful(), 'Transaction should be successful');
        $this->assertNotNull($transaction->getDepositDate(), 'Complete order should have deposit date');
        $this->assertEquals(
            BusinessDaysCalculator::getNextBusinessDate(new \DateTime($itemDateTime))->format('Y-m-d'),
            $transaction->getDepositDate()->format('Y-m-d'),
            'Transaction\'s deposit date is wrong'
        );
    }

    /**
     * @test
     */
    public function shouldTransformDepositItemToOrderWithCustomOperationIfLocationIsFromApplicationFeeDepositAccount()
    {
        $encoder = $this->getEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->with('test')
            ->will($this->returnValue('18'));

        $group = new Group();
        $depositAccount = new DepositAccount();
        $depositAccount->setGroup($group);
        $depositAccount->setType(DepositAccountType::APPLICATION_FEE);
        $depositAccount->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $depositAccount->setMerchantName(102588);
        $depositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        $group->addDepositAccount($depositAccount);

        $contract = new Contract();
        $contract->setTenant(new Tenant());
        $contract->setGroup($group);

        $repository = $this->getContractRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('find')
            ->with('18')
            ->will($this->returnValue($contract));

        $depositItem = new WSRemoteDepositItem();
        $totalAmount = 999.99;
        $itemDateTime = (new \DateTime())->format('Y-m-d');
        $depositItem
            ->setCustomerNumber('test')
            ->setBatchNumber('b1')
            ->setItemStatus(WSItemStatus::SENTTOTRANSACTIONPROCESSING)
            ->setCheckNumber('0201')
            ->setItemDateTime($itemDateTime)
            ->setTotalAmount($totalAmount)
            ->setLocationId(102588)
            ->setReferenceNumber('ref-test');

        $checkTransformer = new ScannedCheckTransformer(
            $encoder,
            $repository,
            $this->getLoggerMock()
        );

        $order = $checkTransformer->transformToOrder($depositItem);
        $this->assertInstanceOf(Order::class, $order, 'Order entity is expected');
        /** @var Operation $operation */
        $this->assertInstanceOf(
            Operation::class,
            $operation = $order->getOperations()->first(),
            'Operation entity is expected'
        );
        $this->assertInstanceOf(
            Transaction::class,
            $transaction = $order->getCompleteTransaction(),
            'Transaction entity is expected'
        );
        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus(), 'Order is expected to be COMPLETE');
        $this->assertEquals($totalAmount, $order->getSum(), sprintf('Order sum should be %s', $totalAmount));
        $this->assertEquals(
            PaymentProcessor::PROFIT_STARS,
            $order->getPaymentProcessor(),
            'Order should have PROFIT_STARS payment processor'
        );
        $this->assertEquals(
            OrderPaymentType::SCANNED_CHECK,
            $order->getPaymentType(),
            'Order should have SCANNED_CHECK type'
        );
        $this->assertEquals(
            '0201',
            $order->getCheckNumber(),
            'Order should have check number 0201'
        );
        $this->assertEquals(
            $itemDateTime,
            $order->getCreatedAt()->format('Y-m-d'),
            'Order should have expected createdAt'
        );
        $this->assertEquals(
            OperationType::CUSTOM,
            $operation->getType(),
            'Operation should have type RENT'
        );
        $this->assertEquals(
            $totalAmount,
            $operation->getTotalAmount(),
            sprintf('Operation amount should be %s', $totalAmount)
        );
        $this->assertEquals(
            $itemDateTime,
            $operation->getCreatedAt()->format('Y-m-d'),
            'Operation should have expected createdAt'
        );
        $this->assertEquals(
            $itemDateTime,
            $operation->getPaidFor()->format('Y-m-d'),
            'Operation should have expected paidFor'
        );
        $this->assertEquals(
            $itemDateTime,
            $transaction->getCreatedAt()->format('Y-m-d'),
            'Transaction should have expected createdAt'
        );
        $this->assertEquals(
            $itemDateTime,
            $transaction->getBatchDate()->format('Y-m-d'),
            'Transaction should have expected batchDate'
        );
        $this->assertEquals(
            102588,
            $transaction->getMerchantName(),
            'Transaction should have merchantName 102588'
        );
        $this->assertEquals(
            'b1',
            $transaction->getBatchId(),
            'Transaction should have batchId b1'
        );
        $this->assertTrue($transaction->getIsSuccessful(), 'Transaction should be successful');
        $this->assertNotNull($transaction->getDepositDate(), 'Complete order should have deposit date');
        $this->assertEquals(
            BusinessDaysCalculator::getNextBusinessDate(new \DateTime($itemDateTime))->format('Y-m-d'),
            $transaction->getDepositDate()->format('Y-m-d'),
            'Transaction\'s deposit date is wrong'
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Skip32IdEncoder
     */
    protected function getEncoderMock()
    {
        return $this->getBaseMock(Skip32IdEncoder::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContractRepository
     */
    protected function getContractRepositoryMock()
    {
        return $this->getBaseMock('RentJeeves\DataBundle\Entity\ContractRepository');
    }
}
