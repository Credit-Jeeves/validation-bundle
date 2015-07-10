<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone;

use ACI\Client\PayAnyone\Enum\BankAccountType;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Payum\AciPayAnyone\Model\NewPayment;
use Payum\AciPayAnyone\Model\SubModel\BankAccount;
use Payum\AciPayAnyone\Model\SubModel\Payee;
use Payum\AciPayAnyone\Model\SubModel\Payer;
use Payum\AciPayAnyone\Request\CaptureRequest\Capture;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Payum\AciPayAnyone\Model\ExistingPayment;
use Payum\AciPayAnyone\Model\SubModel\Address;
use Payum\AciPayAnyone\Request\CancelRequest\Cancel;
use Payum\Core\Payment as PaymentProcessor;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use RentJeeves\DataBundle\Enum\OutboundTransactionStatus;
use RentJeeves\DataBundle\Enum\OutboundTransactionType;

class PaymentManager
{
    const PAYMENT_ACCOUNT_MAX_LENGTH = 60;
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PaymentProcessor
     */
    protected $paymentProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $defaultBankAccount = [
        'routingNumber' => null,
        'accountNumber' => null,
    ];

    /**
     * @param EntityManager $em
     * @param PayumAwareRegistry $payum
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, PayumAwareRegistry $payum, LoggerInterface $logger)
    {
        $this->em = $em;

        $this->paymentProcessor = $payum->getPayment('aci_pay_anyone');

        $this->logger = $logger;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->defaultBankAccount = array_merge($this->defaultBankAccount, $config);
    }

    /**
     * @param OrderPayDirect $order
     * @return string Order status
     * @throws \Exception|PaymentProcessorInvalidArgumentException
     */
    public function executePayment(OrderPayDirect $order)
    {
        $this->validateOrder($order);

        $transaction = $this->getTransaction($order);

        $this->logger->debug(
            sprintf(
                '[ACI PayAnyone Info][Execute]:Trying to create new deposit transaction for order id #%d',
                $order->getId()
            )
        );

        $payment = new NewPayment();

        $bankAccount = new BankAccount();
        // This fields should ignore on ACI side, but we set it default RT-1366
        $bankAccount->setBankAccountType(BankAccountType::CHECKING);
        $bankAccount->setRoutingNumber($this->defaultBankAccount['routingNumber']);
        $bankAccount->setAccountNumber($this->defaultBankAccount['accountNumber']);

        $bankAccount->setMemoLine($this->generateMemoLine($order));

        $payment->setBankAccount($bankAccount);

        $payment->setPayee($this->getPayee($order->getContract()->getGroup()));

        $payment->setPayer($this->getPayer($order->getContract()));

        $payment->setAmount($order->getSum());
        $payment->setDueDate(new \DateTime());
        $payment->setPaymentAccount(
            $order->getContract()->getProperty()->getShrinkAddress(self::PAYMENT_ACCOUNT_MAX_LENGTH)
        );
        $payment->setTransactionId($order->getId());

        $request = new Capture($payment);

        try {
            $this->paymentProcessor->execute($request);
            if ($request->getIsSuccessful() &&
                $transactionId = $request->getModel()->getPaymentStatus()->getPaymentId()
            ) {
                $order->setStatus(OrderStatus::SENDING);
                $transaction->setTransactionId($transactionId);
                $transaction->setMessage(null);
                $transaction->setStatus(OutboundTransactionStatus::SUCCESS);
            } else {
                $this->logger->alert(sprintf('[ACI PayAnyone Error][Execute]:%s', $request->getMessages()));

                $order->setStatus(OrderStatus::ERROR);
                $transaction->setMessage($request->getMessages());
                $transaction->setStatus(OutboundTransactionStatus::ERROR);
            }

            $this->em->flush();
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI PayAnyone Critical Error][Execute]:%s', $e->getMessage()));
            $order->setStatus(OrderStatus::ERROR);
            $transaction->setStatus(OutboundTransactionStatus::ERROR);
            $transaction->setMessage($e->getMessage());
            $this->em->flush();
            throw $e;
        }

        $this->logger->debug(
            sprintf(
                '[ACI PayAnyone Info][Execute]:Created new %s deposit transaction #%s for order id #%d',
                $transaction->getStatus(),
                $transaction->getTransactionId(),
                $order->getId()
            )
        );

        return $order->getStatus();
    }

    /**
     * @param OrderPayDirect $order
     * @throws \Exception|PaymentProcessorInvalidArgumentException
     * @return bool
     */
    public function cancelPayment(OrderPayDirect $order)
    {
        $transaction = $this->getTransaction($order);

        if (!$transaction->getTransactionId()) {
            throw new PaymentProcessorInvalidArgumentException(
                'Transaction doesn\'t have transaction id'
            );
        }

        $this->logger->debug(
            sprintf(
                '[ACI PayAnyone Info][Cancel]:Trying to cancel transaction #%s for order id #%d',
                $transaction->getTransactionId(),
                $order->getId()
            )
        );

        $payment = new ExistingPayment();
        $payment->setPaymentId($transaction->getTransactionId());

        $request = new Cancel($payment);

        try {
            $this->paymentProcessor->execute($request);

            if ($request->getIsSuccessful()) {
                $order->setStatus(OrderStatus::ERROR);
                $transaction->setStatus(OutboundTransactionStatus::CANCELLED);
                $transaction->setMessage('Cancelled by Admin');
                $this->logger->debug(
                    sprintf(
                        '[ACI PayAnyone Info][Cancel]:Cancelled transaction #%s for order id #%d',
                        $transaction->getTransactionId(),
                        $order->getId()
                    )
                );
                $this->em->flush();

                return true;
            }

            $this->logger->alert(sprintf('[ACI PayAnyone Error][Cancel]:%s', $request->getMessages()));
            $transaction->setMessage($request->getMessages());
            $this->em->flush();

            return false;
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI PayAnyone Critical Error][Cancel]:%s', $e->getMessage()));
            throw $e;
        }
    }

    /**
     * @param Group $group
     * @return Payee
     */
    protected function getPayee(Group $group)
    {
        $payee = new Payee();

        $payeeAddress = new Address();
        $payeeAddress->setAddress1($group->getStreetAddress1());
        $payeeAddress->setCity($group->getCity());
        $payeeAddress->setState($group->getState());
        $payeeAddress->setPostalCode($group->getZip());
        $payeeAddress->setCountryCode($group->getCountry());

        $payee->setName($group->getName());
        $payee->setAddress($payeeAddress);

        return $payee;
    }

    /**
     * @param Contract $contract
     * @return Payer
     */
    protected function getPayer(Contract $contract)
    {
        $payer = new Payer();

        $payerAddress = new Address();
        $payerAddress->setAddress1($contract->getTenantRentAddress());
        $payerAddress->setCity($contract->getProperty()->getCity());
        $payerAddress->setState($contract->getProperty()->getArea());
        $payerAddress->setPostalCode($contract->getProperty()->getZip());
        $payerAddress->setCountryCode($contract->getProperty()->getCountry());

        $payer->setPayerId($contract->getId());
        $payer->setFullName($contract->getTenantFullName());
        $payer->setAddress($payerAddress);

        return $payer;
    }

    /**
     * @param OrderPayDirect $order
     * @return string
     */
    protected function generateMemoLine(OrderPayDirect $order)
    {
        /** @var Operation $operation */
        if ($operation = $order->getRentOperations()->first()) {
            return strtoupper(sprintf(
                '%s rent,#%d',
                $operation->getPaidFor() ? $operation->getPaidFor()->format('M') : '',
                $order->getId()
            ));
        }

        return sprintf(
            'OTHER #%d',
            $order->getId()
        );
    }

    /**
     * @param OrderPayDirect $order
     * @return OutboundTransaction
     */
    protected function getTransaction(OrderPayDirect $order)
    {
        if (!$transaction = $order->getDepositOutboundTransaction()) {
            $transaction = new OutboundTransaction();
            $transaction->setType(OutboundTransactionType::DEPOSIT);
            $transaction->setOrder($order);
            $order->addOutboundTransaction($transaction);

            $this->em->persist($transaction);
        }

        $transaction->setAmount($order->getSum());

        return $transaction;
    }

    /**
     * @param OrderPayDirect $order
     * @throw PaymentProcessorInvalidArgumentException
     */
    protected function validateOrder(OrderPayDirect $order)
    {
        if (!($order->hasContract()) || !$order->getContract()->getGroup()) {
            throw new PaymentProcessorInvalidArgumentException('Order should have contract and group');
        }
    }
}
