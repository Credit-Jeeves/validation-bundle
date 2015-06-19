<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone;

use ACI\Client\PayAnyone\Enum\BankAccountType;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Payum\AciPayAnyone\Model\NewPayment;
use Payum\AciPayAnyone\Model\SubModel\BankAccount;
use Payum\AciPayAnyone\Model\SubModel\Payee;
use Payum\AciPayAnyone\Model\SubModel\Payer;
use Payum\AciPayAnyone\Request\CaptureRequest\Capture;
use Psr\Log\LoggerInterface;
use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Payum\AciPayAnyone\Model\ExistingPayment;
use Payum\AciPayAnyone\Model\SubModel\Address;
use Payum\AciPayAnyone\Request\CancelRequest\Cancel;
use Payum\Core\Payment as PaymentProcessor;
use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry as PayumAwareRegistry;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use RentJeeves\DataBundle\Enum\OutboundTransactionStatus;
use RentJeeves\DataBundle\Enum\OutboundTransactionType;

class PaymentManager
{
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
     * @var OutboundTransaction
     */
    protected $transaction;

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
     * @param Order $order
     * @return string Order status
     * @throws \Exception|PaymentProcessorInvalidArgumentException
     */
    public function executePayment(Order $order)
    {
        $this->validateOrder($order);

        $this->logger->debug(
            sprintf(
                '[ACI PayAnyone Info][Execute]:Try to create new deposit transaction for order id #%d',
                $this->getTransaction($order)->getTransactionId(),
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
        $payment->setPaymentAccount($order->getContract()->getProperty()->getShrinkAddress());
        $payment->setTransactionId($order->getId());

        $request = new Capture($payment);

        try {
            $this->paymentProcessor->execute($request);
            if ($request->getIsSuccessful() &&
                $transactionId = $request->getModel()->getPaymentStatus()->getPaymentId()
            ) {
                $order->setStatus(OrderStatus::SENDING);
                $this->getTransaction($order)->setTransactionId($transactionId);
                $this->getTransaction($order)->setMessage(null);
                $this->getTransaction($order)->setStatus(OutboundTransactionStatus::SUCCESS);
            } else {
                $this->logger->alert(sprintf('[ACI PayAnyone Error][Execute]:%s', $request->getMessages()));

                $order->setStatus(OrderStatus::ERROR);
                $this->getTransaction($order)->setMessage($request->getMessages());
                $this->getTransaction($order)->setStatus(OutboundTransactionStatus::ERROR);
            }

            $this->em->flush();
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('[ACI PayAnyone Critical Error][Execute]:%s', $e->getMessage()));
            $order->setStatus(OrderStatus::ERROR);
            $this->getTransaction($order)->setStatus(OutboundTransactionStatus::ERROR);
            $this->getTransaction($order)->setMessage($e->getMessage());
            if ($this->em->isOpen()) {
                $this->em->flush();
            }
            throw $e;
        }

        $this->logger->debug(
            sprintf(
                '[ACI PayAnyone Info][Execute]:Created new %s deposit transaction for order id #%d',
                $this->getTransaction($order)->getStatus(),
                $order->getId()
            )
        );

        return $order->getStatus();
    }

    /**
     * @param Order $order
     * @throws \Exception|PaymentProcessorRuntimeException
     */
    public function cancelPayment(Order $order)
    {
        $this->logger->debug(
            sprintf(
                '[ACI PayAnyone Info][Cancel]:Try to cancel transaction #%s for order id #%d',
                $this->getTransaction($order)->getTransactionId(),
                $order->getId()
            )
        );
        $payment = new ExistingPayment();
        $payment->setTransactionId($this->getTransaction($order)->getTransactionId());

        $request = new Cancel($payment);

        try {
            $this->paymentProcessor->execute($request);

            if ($request->getIsSuccessful()) {
                $order->setStatus(OrderStatus::ERROR);
                $this->getTransaction($order)->setStatus(OutboundTransactionStatus::CANCELLED);
                $this->getTransaction($order)->setMessage('Cancelled by Admin');
                $this->logger->debug(
                    sprintf(
                        '[ACI PayAnyone Info][Cancel]:Cancelled transaction #%s for order id #%d',
                        $this->getTransaction($order)->getTransactionId(),
                        $order->getId()
                    )
                );
            } else {
                $this->getTransaction($order)->setStatus(OutboundTransactionStatus::ERROR);
                $this->getTransaction($order)->setMessage($request->getMessages());
                throw new PaymentProcessorRuntimeException('Can\'t cancelled transaction: ' . $request->getMessages());
            }

            $this->em->flush();
        } catch (PaymentProcessorRuntimeException $e) {
            $this->logger->alert(sprintf('[ACI PayAnyone Error][Cancel]:%s', $request->getMessages()));
            throw $e;
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
     * @param Order $order
     * @return string
     */
    protected function generateMemoLine(Order $order)
    {
        if ($operation = $order->getRentOperations()->first()) {
            /** @var Operation $operation */

            return strtoupper(sprintf(
                '%s %s,#%d',
                $operation->getPaidFor() ? $operation->getPaidFor()->format('M') : '',
                $operation->getType(),
                $order->getId()
            ));
        }

        return sprintf(
            'OTHER #%d',
            $order->getId()
        );
    }

    /**
     * @param Order $order
     * @return OutboundTransaction
     */
    protected function getTransaction(Order $order)
    {
        if (is_null($this->transaction)) {
            if (!$this->transaction = $order->getDepositOutboundTransaction()) {
                $this->transaction = new OutboundTransaction();
                $this->transaction->setType(OutboundTransactionType::DEPOSIT);
                $this->transaction->setOrder($order);
                $order->addOutboundTransaction($this->transaction);

                $this->em->persist($this->transaction);
            }

            $this->transaction->setAmount($order->getSum());
        }

        return $this->transaction;
    }

    /**
     * @param Order $order
     * @throw PaymentProcessorInvalidArgumentException
     */
    protected function validateOrder(Order $order)
    {
        if (!($order->hasContract()) || !$order->getContract()->getGroup()) {
            throw new PaymentProcessorInvalidArgumentException('Order should have contract and group');
        }
    }
}
