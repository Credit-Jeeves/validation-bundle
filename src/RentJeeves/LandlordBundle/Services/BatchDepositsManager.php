<?php

namespace RentJeeves\LandlordBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use RentJeeves\DataBundle\Entity\TransactionRepository;

class BatchDepositsManager
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * @var array
     */
    protected $enumForDepositTypeArrayToReplaceForReadable = [
        'security_deposit' => 'Security Deposit',
        'application_fee' => 'Application Fee',
        'rent' => 'Rent'
    ];

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
    public function getCountDeposits(Group $group, $filter)
    {
        return $this->getTransactionRepository()->getCountDeposits($group, $filter);
    }

    /**
     * @param Group $group
     * @param string $filter
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getDeposits(Group $group, $filter, $page = 1, $limit = 10)
    {
        $deposits = $this->getTransactionRepository()->getBatchedDeposits($group, $filter, $page, $limit);

        foreach ($deposits as $key => $deposit) {
            $depositDate = new \DateTime($deposit['depositDate']);
            $deposits[$key]['depositDate'] = $depositDate->format('m/d/Y');
            $depositType = $deposits[$key]['depositType'];
            if (isset($this->enumForDepositTypeArrayToReplaceForReadable[$depositType])) {
                $deposits[$key]['depositType'] = $this->enumForDepositTypeArrayToReplaceForReadable[$depositType];
            }
            $orders = $this->getOrderRepository()->getDepositedOrders(
                $group,
                $filter,
                $deposit['batchNumber'],
                $deposit['depositDate']
            );

            $deposits[$key]['orders'] = $orders;
        }

        return $deposits;
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
