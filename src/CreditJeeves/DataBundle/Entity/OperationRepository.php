<?php

namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use \DateTime;

class OperationRepository extends EntityRepository
{
    public function getExperianRentOperationsForMonth($contractId, $monthNo, $yearNo)
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

        return $query->execute();
    }

    public function getTransUnionRentOperationsForMonth($contractId, $monthNo, $yearNo)
    {
        $query = $this->createQueryBuilder('op');
        $query->select('sum(op.amount) total_amount, max(op.createdAt) last_payment_date, op.paidFor paid_for');
        $query->innerJoin('op.contract', 'c');
        $query->innerJoin('op.order', 'ord', Expr\Join::WITH, 'ord.status = :orderStatus');
        $query->where('c.id = :contractId');
        $query->andWhere('op.type = :operationType');
        $query->andWhere('MONTH(op.paidFor) = :month');
        $query->andWhere('YEAR(op.paidFor) = :year');
        $query->groupBy('op.paidFor');
        $query->setParameter('contractId', $contractId);
        $query->setParameter('operationType', OperationType::RENT);
        $query->setParameter('orderStatus', OrderStatus::COMPLETE);
        $query->setParameter('month', $monthNo);
        $query->setParameter('year', $yearNo);
        $query = $query->getQuery();

        return $query->getScalarResult();
    }

    public function getOperationForImport(
        Tenant $tenant,
        Contract $contract,
        DateTime $paidFor,
        $amount
    ) {
        $query = $this->createQueryBuilder("operation");
        $query->innerJoin("operation.order", "ord");
        $query->innerJoin("operation.contract", "contract");
        $query->innerJoin("contract.tenant", "tenant");
        $query->where("tenant.id = :tenant");
        $query->andWhere("ord.status = :status");
        $query->andWhere("operation.amount = :amount");
        $query->andWhere("contract.id = :contract");
        $query->andWhere("MONTH(operation.paidFor) = :paidForMonth");
        $query->andWhere("YEAR(operation.paidFor) = :paidForYear");

        $query->setParameter("amount", $amount);
        $query->setParameter("contract", $contract->getId());
        $query->setParameter("paidForMonth", $paidFor->format("n"));
        $query->setParameter("paidForYear", $paidFor->format("Y"));
        $query->setParameter("tenant", $tenant->getId());
        $query->setParameter("status", OrderStatus::COMPLETE);

        $query->setMaxResults(1);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }
}
