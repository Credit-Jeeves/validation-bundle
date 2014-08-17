<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use DateTime;

class HeartlandRepository extends EntityRepository
{
    /**
     * @param Group $group
     * @param DateTime $date
     * @return mixed
     */
    public function getBatchDepositedInfo($group, DateTime $date)
    {
        $query = $this->createQueryBuilder('h');
        $query->select(
            "h.batchId,
            h.transactionId,
            h.amount,
            date_format(h.createdAt, '%m/%d/%Y') as dateInitiated,
            o.type as paymentType,
            o.status,
            CONCAT_WS(' ', ten.first_name, ten.last_name) as resident,
            CONCAT_WS(' ', prop.number, prop.street) as property,
            prop.isSingle,
            unit.name as unitName"
        );
        $query->orderBy('h.batchId', 'DESC');
        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('t.unit', 'unit');

        $query->where('t.group = :group');
        $query->setParameter('group', $group);

        $query->andWhere('h.depositDate = DATE(:date)');
        $query->setParameter('date', $date);

        $query->andWhere('h.batchId IS NOT NULL');
        $query->andWhere('h.isSuccessful = 1');

        /** Now we select only completed transaction */
        $query->andWhere('o.status = :status');
        $query->setParameter('status', OrderStatus::COMPLETE);

        return $query->getQuery()->execute();
    }
}
