<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\OrderStatus;

class HeartlandRepository extends EntityRepository
{
    /**
     * @param Group $group
     * @param null $date
     * @return mixed
     */
    public function getBatchDepositedInfo($group, $date = null)
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
            CONCAT(prop.number, ' ', prop.street, ' #',unit.name) as property"
        );
        $query->orderBy('h.batchId', 'DESC');
        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('t.unit', 'unit');
        if ($group instanceof Group) {
            $query->where('t.group = :group');
            $query->setParameter('group', $group);
        }
        $query->andWhere('h.batchId IS NOT NULL');
        $query->andWhere('h.isSuccessful = 1');

        /** Now we select only completed transaction */
        $query->andWhere('o.status = :status');
        $query->setParameter('status', OrderStatus::COMPLETE);

        if ($date) {
            $query->andWhere('h.depositDate = DATE(:date)');
            $query->setParameter('date', $date);
        }
        return $query->getQuery()->execute();
    }
}
