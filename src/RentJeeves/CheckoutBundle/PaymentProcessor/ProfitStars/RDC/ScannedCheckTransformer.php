<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Psr\Log\LoggerInterface;
use RentJeeves\ApiBundle\Services\Encoders\Skip32IdEncoder;
use RentJeeves\ApiBundle\Services\Encoders\ValidationEncoderException;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\ProfitStarsTransaction;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSItemStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositItem;

/**
 * Service "payment_processor.profit_stars.rdc.check_transformer"
 */
class ScannedCheckTransformer
{
    /** @var Skip32IdEncoder */
    protected $encoder;

    /** @var ContractRepository */
    protected $contractRepository;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param Skip32IdEncoder $encoder
     * @param ContractRepository $repository
     * @param LoggerInterface $logger
     */
    public function __construct(Skip32IdEncoder $encoder, ContractRepository $repository, LoggerInterface $logger)
    {
        $this->encoder = $encoder;
        $this->contractRepository = $repository;
        $this->logger = $logger;
    }

    /**
     * @param WSRemoteDepositItem $depositItem
     * @return OrderSubmerchant
     * @throws ProfitStarsException
     */
    public function transformToOrder(WSRemoteDepositItem $depositItem)
    {
        $contract = $this->getContract($depositItem);

        $order = new OrderSubmerchant();
        $order->setSum($depositItem->getTotalAmount());
        $order->setUser($contract->getTenant());
        $order->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $order->setPaymentType(OrderPaymentType::SCANNED_CHECK);
        $order->setCheckNumber($depositItem->getCheckNumber());
        if ($depositAccount = $this->getDepositAccount($contract->getGroup(), $depositItem->getLocationId())) {
            $order->setDepositAccount($depositAccount);
        }
        $createdDate = new \DateTime($depositItem->getItemDateTime());
        $order->setCreatedAt($createdDate);
        $order->setStatus(OrderStatus::PENDING);

        $operation = new Operation();
        $operation->setOrder($order);
        $operationType = OperationType::RENT;
        if (null !== $depositAccount && $depositAccount->getType() !== DepositAccountType::RENT) {
            $operationType = OperationType::CUSTOM;
        }
        $operation->setType($operationType);
        $operation->setContract($contract);
        $operation->setAmount($depositItem->getTotalAmount());
        $operation->setPaidFor($this->calculatePaidFor($contract, $createdDate));
        $operation->setCreatedAt($createdDate);
        $order->addOperation($operation);

        $transaction = new Transaction();
        $transaction->setOrder($order);
        $transaction->setMerchantName($depositItem->getLocationId());
        $transaction->setBatchId($depositItem->getBatchNumber());
        $transaction->setBatchDate($createdDate);
        $transaction->setDepositDate(BusinessDaysCalculator::getNextDepositDate(clone $createdDate));
        $transaction->setAmount($depositItem->getTotalAmount());
        $transaction->setIsSuccessful(true);
        $transaction->setTransactionId($depositItem->getReferenceNumber());
        $transaction->setCreatedAt($createdDate);

        if (WSItemStatus::SENTTOTRANSACTIONPROCESSING === $depositItem->getItemStatus()) {
            $order->setStatus(OrderStatus::COMPLETE);
        }
        $order->addTransaction($transaction);

        $profitStarsTransaction = new ProfitStarsTransaction();
        $profitStarsTransaction->setOrder($order);
        $profitStarsTransaction->setItemId($depositItem->getItemId());

        $order->setProfitStarsTransaction($profitStarsTransaction);

        return $order;
    }

    /**
     * Will be used in ReportLoader
     */
    public function transformToDepositReportTransaction()
    {

    }

    /**
     * @param WSRemoteDepositItem $depositItem
     * @return Contract
     * @throws ProfitStarsException
     */
    protected function getContract(WSRemoteDepositItem $depositItem)
    {
        try {
            $contractId = $this->encoder->decode($depositItem->getCustomerNumber());
        } catch (ValidationEncoderException $e) {
            throw new ProfitStarsException(sprintf(
                'Customer number %s is invalid, can not skip32 decode.',
                $depositItem->getCustomerNumber()
            ));
        }

        $contract = $this->contractRepository->find($contractId);
        if (null === $contract) {
            throw new ProfitStarsException(sprintf(
                'Contract not found for customerNumber "%s" (contractId #%s), batchNumber "%s"',
                $depositItem->getCustomerNumber(),
                $contractId,
                $depositItem->getBatchNumber()
            ));
        }

        return $contract;
    }

    /**
     * @param Group $group
     * @param string $locationId
     * @return null|DepositAccount
     */
    protected function getDepositAccount(Group $group, $locationId)
    {
        foreach ($group->getDepositAccounts() as $depositAccount) {
            if (PaymentProcessor::PROFIT_STARS === $depositAccount->getPaymentProcessor() &&
                $locationId == $depositAccount->getMerchantName()
            ) {
                return $depositAccount;
            }
        }

        return null;
    }

    /**
     * If <= 15th, set paid for to contract due date for current month.
     * If > 15th, set paid for to contract due date for next month.
     *
     * @param Contract $contract
     * @param \DateTime $date
     * @return \DateTime
     */
    protected function calculatePaidFor(Contract $contract, \DateTime $date)
    {
        $paidFor = clone $date;
        $currentDay = $date->format('j');
        if ($currentDay > 15) {
            $paidFor->modify('+1 month');
        }
        $paidFor->setDate($paidFor->format('Y'), $paidFor->format('n'), $contract->getDueDate());

        return $paidFor;
    }
}
