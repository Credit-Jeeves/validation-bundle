<?php

namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class OperationRepository extends EntityRepository
{
    public function getRentOperationForMonth($contractId, $monthNo, $yearNo)
    {
        $query = $this->createQueryBuilder('op');
        $query->innerJoin('op.contract', 'c');
        $query->innerJoin('op.order', 'ord', Expr\Join::WITH, 'ord.status = :orderStatus');
        $query->where('c.id = :contractId');
        $query->andWhere('op.type = :operationType');
        $query->andWhere('MONTH(op.paidFor) = :month');
        $query->andWhere('YEAR(op.paidFor) = :year');
        $query->setParameter('contractId', $contractId);
        $query->setParameter('operationType', OperationType::RENT);
        $query->setParameter('orderStatus', OrderStatus::COMPLETE);
        $query->setParameter('month', $monthNo);
        $query->setParameter('year', $yearNo);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }
}
