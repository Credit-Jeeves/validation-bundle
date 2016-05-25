<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

class GroupRepository extends EntityRepository
{
    /**
     *
     * @param Group $currentGroup
     * @param mixed $groups
     * @param string $searchField
     * @param string $searchString
     *
     * @return Group[]
     */
    public function searchGroupsPerContractFilter(
        Group $currentGroup,
        $groups,
        $searchField = '',
        $searchString = ''
    ) {
        if (empty($groups)) {
            return [];
        }

        $allowedFieldsToSearch = [
            'tenant',
            'tenantA',
            'email'
        ];

        if (!in_array($searchField, $allowedFieldsToSearch)) {
            return [];
        }

        $groupsId = [];
        /** @var Group $group */
        foreach ($groups as $group) {
            $groupsId[] = $group->getId();
        }

        $query = $this->createQueryBuilder('g')
            ->innerJoin('g.contracts', 'c')
            ->innerJoin('c.property', 'p')
            ->innerJoin('c.tenant', 't')
            ->where('g.id IN (:groups)')
            ->groupBy('c.group')
            ->andWhere('c.status <> :status')
            ->andWhere('g.id <> :currentGroup')
            ->setParameter('currentGroup', $currentGroup->getId())
            ->setParameter('status', ContractStatus::DELETED)
            ->setParameter('groups', $groupsId);

        if (!empty($searchField) && !empty($searchString)) {
            $search = preg_replace('/\s+/', ' ', trim($searchString));
            $search = explode(' ', $search);
            switch ($searchField) {
                case 'tenant':
                case 'tenantA':
                    foreach ($search as $item) {
                        $query->andWhere('CONCAT(t.first_name, t.last_name) LIKE :search')
                              ->setParameter('search', '%' . $item . '%');
                    }
                    break;
                case 'email':
                    $query->andWhere('t.email LIKE :search')
                          ->setParameter('search', '%' . $searchString . '%');
                    break;
            }
        }

        return $query->getQuery()->execute();
    }

    /**
     * @param Holding $holding
     *
     * @return Group[]
     */
    public function getGroupsWithoutDepositAccount(Holding $holding)
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.depositAccounts', 'da')
            ->where("g.holding = :holdingId")
            ->andWhere("da.id IS NULL")
            ->setParameter('holdingId', $holding->getId())
            ->getQuery()
            ->execute();
    }

    public function getGroupsWithPendingContracts(Holding $holding)
    {
        $query = $this->createQueryBuilder('g');
        $query->select('g.name as group_name, count(g.id) as amount_pending');
        $query->innerJoin('g.contracts', 'c');
        $query->where("g.holding = :holdingId");
        $query->andWhere("c.status = :statusPending");
        $query->groupBy('g.id');
        $query->setParameter('holdingId', $holding->getId());
        $query->setParameter('statusPending', ContractStatus::PENDING);
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getCountPendingContracts(Group $group)
    {
        $query = $this->createQueryBuilder('g');
        $query->select('count(g.id) as amount_pending');
        $query->innerJoin('g.contracts', 'c');
        $query->where("g.id = :groupId");
        $query->andWhere("c.status = :statusPending");
        $query->setParameter('groupId', $group->getId());
        $query->setParameter('statusPending', ContractStatus::PENDING);
        $query = $query->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param $accountNumber
     * @param  Holding                                $holding
     * @return null|Group
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getGroupByAccountNumber($accountNumber, Holding $holding)
    {
        $groups = $this->createQueryBuilder('g')
            ->join('g.depositAccounts', 'd')
            ->where('d.accountNumber = :accountNumber')
            ->andWhere('d.holding = :holding')
            ->setParameter('accountNumber', $accountNumber)
            ->setParameter('holding', $holding)
            ->getQuery()
            ->execute();

        if (count($groups) > 1) {
            throw new \Exception(
                sprintf(
                    'Something wrong with index for accountNumber. Please check data: accountNumber %s holdingId %s',
                    $accountNumber,
                    $holding->getId()
                )
            );
        }

        if (empty($groups)) {
            return null;
        }

        return reset($groups);
    }

    /**
     * @param Holding $holding
     *
     * @return Group[]
     */
    public function getAllGroupIdsInHolding(Holding $holding)
    {
        return $this->createQueryBuilder('g')
            ->select('g.id')
            ->where('g.holding = :holding')
            ->setParameter('holding', $holding)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Group[]
     */
    public function getProfitStarsEnabledGroups()
    {
        return $this->createQueryBuilder('g')
            ->innerJoin('g.holding', 'h')
            ->innerJoin('h.profitStarsSettings', 'ps')
            ->innerJoin('g.depositAccounts', 'da')
            ->innerJoin('g.contracts', 'c') // to drop out groups without contracts
            ->where('ps.merchantId IS NOT NULL')
            ->andWhere('da.paymentProcessor = :profitStars')
            ->andWhere('da.status = :completeStatus')
            ->andWhere('da.merchantName IS NOT NULL')
            ->setParameter('profitStars', PaymentProcessor::PROFIT_STARS)
            ->setParameter('completeStatus', DepositAccountStatus::DA_COMPLETE)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $locationId
     *
     * @return Group|null
     */
    public function getGroupByExternalLocationId($locationId)
    {
        return $this->createQueryBuilder('g')
            ->innerJoin('g.trustedLandlord', 'tl')
            ->innerJoin('tl.checkMailingAddress', 'cma')
            ->where('cma.externalLocationId = :locationId')
            ->setParameter('locationId', $locationId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
