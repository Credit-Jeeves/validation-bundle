<?php

namespace RentJeeves\LandlordBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderRepository;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\TransactionStatus;

class BatchDepositsManager
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Group $group
     * @param string $filter
     * @return int
     */
    public function getCountDeposits(Group $group, $filter, $search)
    {
        return $this->getTransactionRepository()->getCountDeposits($group, $this->getFilter($filter), $search);
    }

    /**
     * @param Group $group
     * @param string $filter
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getDeposits(Group $group, $filter, $search, $page = 1, $limit = 10)
    {
        $deposits = $this
            ->getTransactionRepository()
            ->getBatchedDeposits($group, $this->getFilter($filter), $search, $page, $limit);

        foreach ($deposits as $key => $deposit) {
            $depositDate = new \DateTime($deposit['depositDate']);
            if ($deposits[$key]['friendlyName']) {
                $depositType = $deposits[$key]['friendlyName'];
            } else {
                $depositType = DepositAccountType::capitalizeTitle($deposits[$key]['depositType']);
            }
            $deposits[$key]['depositDate'] = $depositDate->format('m/d/Y');
            $deposits[$key]['depositType'] = $depositType;
            $deposits[$key]['orderAmount'] = $this->formatAmount(
                $this->getBatchAmount($deposits[$key]['orderAmount'], $deposits[$key]['status'])
            );
            $deposits[$key]['orders'] = $this->getBatchedTransactions($deposit['batchNumber'], $deposit['status']);
        }

        return $deposits;
    }

    protected function getBatchedTransactions($batchId, $batchType)
    {
        $result = [];
        $transactions = $this->getTransactionRepository()->getTransactionsForBatch($batchId);
        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            /** @var Contract $contract */
            $contract = $transaction->getOrder()->getContract();
            $result[] = [
                'tenant' => $contract ? $contract->getTenant()->getFullName() : '',
                'address' => $contract ? $contract->getRentAddress() : '',
                'amount' => $this->formatAmount($this->getTransactionAmountForBatch($transaction, $batchType)),
                'depositDate' => $transaction->getDepositDate() ?
                    $transaction->getDepositDate()->format('m/d/Y') : 'N/A',
                'depositType' => $transaction->getOrder()->getDepositAccount() ?
                    DepositAccountType::title($transaction->getOrder()->getDepositAccount()->getType()) : '',
                'transactionId' => $transaction->getTransactionId(),
                'errorMessage' => $transaction->getMessages(),
                'style' => $this->getOrderStatusStyle($transaction->getOrder()),
                'status' => 'order.status.text.'.$transaction->getOrder()->getStatus(),
            ];
        }

        return $result;
    }

    /**
     * @param float $batchAmount
     * @param string $batchType
     * @return float
     */
    protected function getBatchAmount($batchAmount, $batchType)
    {
        if ($batchType === TransactionStatus::REVERSED) {
            $batchAmount *= -1.0;
        }

        return $batchAmount;
    }

    /**
     * @param Transaction $transaction
     * @param $batchType
     * @return float
     */
    protected function getTransactionAmountForBatch(Transaction $transaction, $batchType)
    {
        $amount = $transaction->getOrder()->getSum();
        if ($batchType === TransactionStatus::REVERSED) {
            $amount *= -1.0;
        }

        return $amount;
    }

    /**
     * Check filter value before requesting to DB.
     *
     * @param string $givenFilter
     * @return string
     */
    protected function getFilter($givenFilter)
    {
        if ('transactionId' === $givenFilter || 'batchId' === $givenFilter) {
            return $givenFilter;
        }

        return '';
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getOrderStatusStyle(Order $order)
    {
        switch ($order->getStatus()) {
            case OrderStatus::COMPLETE:
                $style = '';
                break;
            case OrderStatus::ERROR:
            case OrderStatus::CANCELLED:
            case OrderStatus::REFUNDED:
            case OrderStatus::RETURNED:
                $style = 'late';
                break;
            default:
                $style = 'contract-pending';
        }

        return $style;
    }

    /**
     * @param float $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * @return TransactionRepository
     */
    protected function getTransactionRepository()
    {
        return $this->em->getRepository('RjDataBundle:Transaction');
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->em->getRepository('DataBundle:Order');
    }
}
