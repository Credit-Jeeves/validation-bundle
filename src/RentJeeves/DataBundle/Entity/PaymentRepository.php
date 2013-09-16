<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\PaymentStatus;

/**
 * 
 * @author Alex Emelyanov
 * Aliases for this class
 * p - payment, table rj_payment, class Payment
 * c - contract, table rj_contract, class Contract
 * t - tenant, table cj_user, class Tenant
 * g - group, table cj_account_group, class Group
 *
 */
class PaymentRepository extends EntityRepository
{
    public function getActivePayments()
    {
        $query = $this->createQueryBuilder('p');
        $query->where('p.status = :status');
        $query->setParameter('status', PaymentStatus::ACTIVE);
        $query = $query->getQuery();
        return $query->execute();
    }
}
