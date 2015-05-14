<?php

namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use \DateTime;
use RentJeeves\DataBundle\Enum\TransactionStatus;

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
        // TODO: Consider lease closures with no payments this month! RT-1299
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
        \DateTime $paidFor
    ) {
        $query = $this->createQueryBuilder("operation");
        $query->innerJoin("operation.order", "ord");
        $query->innerJoin("operation.contract", "contract");
        $query->innerJoin("contract.tenant", "tenant");
        $query->where("tenant.id = :tenant");
        $query->andWhere("ord.status = :complete");
        $query->andWhere("operation.type = :operationType");
        $query->andWhere("contract.id = :contract");
        $query->andWhere("MONTH(operation.paidFor) = :paidForMonth");
        $query->andWhere("YEAR(operation.paidFor) = :paidForYear");

        $query->setParameter("operationType", OperationType::RENT);
        $query->setParameter("contract", $contract->getId());
        $query->setParameter("paidForMonth", $paidFor->format("n"));
        $query->setParameter("paidForYear", $paidFor->format("Y"));
        $query->setParameter("tenant", $tenant->getId());
        $query->setParameter("complete", OrderStatus::COMPLETE);

        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getOperationsForXmlReport(
        Property $property,
        Holding $holding,
        $start,
        $end
    ) {
        $query = $this->createQueryBuilder('operation')->select(
            'operation,
             ord,
             prop,
             contract,
             tenant,
             unit'
        );
        $query->innerJoin("operation.order", "ord");
        $query->innerJoin("operation.contract", "contract");
        $query->innerJoin("contract.tenant", "tenant");
        $query->innerJoin("tenant.residentsMapping", "resident");
        $query->innerJoin('contract.property', 'prop');
        $query->innerJoin('contract.unit', 'unit');
        $query->innerJoin('ord.transactions', 'transaction');

        $query->where("transaction.depositDate BETWEEN :start AND :end");
        $query->andWhere("transaction.depositDate IS NOT NULL");
        $query->andWhere("transaction.batchId IS NOT NULL");
        $query->andWhere('transaction.isSuccessful = 1');
        $query->andWhere('contract.property = :property');
        $query->andWhere('resident.holding = :holding');
        $query->andWhere('ord.status IN (:statuses)');
        $query->andWhere('operation.type = :type1 OR operation.type = :type2');
        $query->andWhere('operation.amount > 0');
        $query->andWhere('transaction.status = :completeTransaction');
        $query->orderBy('ord.id', 'ASC');

        $query->setParameter('end', $end);
        $query->setParameter('holding', $holding);
        $query->setParameter('type1', OperationType::RENT);
        $query->setParameter('type2', OperationType::OTHER);
        $query->setParameter('start', $start);
        $query->setParameter('property', $property);
        $query->setParameter('statuses', [OrderStatus::COMPLETE, OrderStatus::REFUNDED, OrderStatus::RETURNED]);
        $query->setParameter('completeTransaction', TransactionStatus::COMPLETE);

        $query = $query->getQuery();

        return $query->execute();
    }
}
