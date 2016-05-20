<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Doctrine\ORM\Query\Expr;

class TenantRepository extends EntityRepository
{
    /**
     * @param Tenant $tenant
     * @return bool
     */
    public function isPaymentProcessorLocked(Tenant $tenant)
    {
        $query = $this->createQueryBuilder('t');
        $query->innerJoin('t.contracts', 'con');
        $query->innerJoin('con.holding', 'h');
        $query->where('con.tenant = :tenant');
        $query->andWhere('h.isPaymentProcessorLocked = 1');
        $query->andWhere('con.status != :deleted AND con.status != :finished');
        $query->setParameter('tenant', $tenant);
        $query->setParameter('deleted', ContractStatus::DELETED);
        $query->setParameter('finished', ContractStatus::FINISHED);
        $query->setMaxResults(1);
        $query = $query->getQuery();

        return count($query->getScalarResult()) > 0;
    }

    public function countTenants($group, $searchBy = 'address', $search = '')
    {
        $query = $this->createQueryBuilder('t');
        $query->innerJoin('t.contracts', 'c');
        $query->groupBy('t.id');
        $query->where('c.group = :group');
        $query->setParameter('group', $group);
        if (!empty($search)) {
//             $query->andWhere('p.'.$searchBy.' = :search');
//             $query->setParameter('search', $search);
        }
        $query = $query->getQuery();

        return $query->getScalarResult();
    }

    public function getTenantsPage(
        $group,
        $page = 1,
        $limit = 100,
        $sort = 'first_name',
        $order = 'ASC',
        $searchBy = 'first_name',
        $search = ''
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('t');
        $query->innerJoin('t.contracts', 'c');
        $query->groupBy('t.id');
        $query->where('c.group = :group');
        $query->setParameter('group', $group);
        if (!empty($search)) {
//             $query->andWhere('p.'.$searchBy.' = :search');
//             $query->setParameter('search', $search);
        }
        $query->orderBy('t.'.$sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     *
     * Don't use for now, but saved, maybe we can use it in future
     * because have problem with doctrine cache for different unit
     *
     * @param $email
     * @param $propertyId
     * @param $unitName
     * @return mixed
     */
    public function getTenantForImport($email, $propertyId, $unitName)
    {
        $query = $this->createQueryBuilder('tenant')
            ->addSelect(
                array('contract', 'unit')
            );
        $query->leftJoin(
            'tenant.contracts',
            'contract',
            Expr\Join::WITH,
            'contract.id IN (
                   SELECT contract2.id
                   FROM RjDataBundle:Tenant tenant2
                   INNER JOIN tenant2.contracts contract2
                   INNER JOIN contract2.unit unit2
                   WHERE tenant2.email = :email AND (
                        contract2.status = :current OR contract2.status = :approved
                   ) AND unit2.name = :unitName
                   AND contract2.property = :property
            )'
        );
        $query->leftJoin(
            'contract.unit',
            'unit'
        );

        $query->where('tenant.email = :email');
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('approved', ContractStatus::APPROVED);
        $query->setParameter('property', $propertyId);
        $query->setParameter('unitName', $unitName);
        $query->setParameter('email', $email);
        $query->setMaxResults(1);
        $query = $query->getQuery();

        $result = $query->getResult();

        return reset($result);
    }

    /**
     *
     * Find Tenant by resident ID or email address
     *
     * @param $email
     * @param $residentId
     * @param $holdingId
     * @throws NonUniqueResultException if more than one Tenant object found
     *
     * @return Tenant object or NULL
     */
    public function getTenantForImportWithResident($email, $residentId, $holdingId)
    {
        $result = null;

        // Find by resident ID if we have one
        if (!empty($residentId)) {
            $query = $this->createQueryBuilder('tenant');
            $query->innerJoin(
                'tenant.residentsMapping',
                'resident'
            );
            $query->where('resident.residentId = :residentId');
            $query->andWhere('resident.holding = :holdingId');
            $query->setParameter('residentId', $residentId);
            $query->setParameter('holdingId', $holdingId);
            $query = $query->getQuery();
            $result = $query->getOneOrNullResult(); // throws exception if more than one
        }

        // If we didn't find by resident ID, try finding by email
        if (!empty($email) && !$result) {
            $query = $this->createQueryBuilder('tenant');
            $query->where('tenant.email = :email');
            $query->setParameter('email', $email);
            $query = $query->getQuery();
            $result = $query->getOneOrNullResult(); // throws exception if more than one
        }

        return $result;
    }

    public function findByHolding($holdingId = null)
    {
        $query = $this->createQueryBuilder('tenant');
        $query->select('distinct tenant.id, tenant.email');

        $query->innerJoin(
            'tenant.contracts',
            'contract'
        );

        $query->orderBy('tenant.email', 'ASC');
        $query->where('contract.holding = :holdingId');
        $query->setParameter('holdingId', $holdingId);

        return $query;
    }

    public function getTenantByIdOrByHolding($tenantId = null, $holdingId = null)
    {
        $query = $this->createQueryBuilder('tenant');

        $query->orderBy('tenant.email', 'ASC');
        if ($tenantId) {
            $query->where('tenant.id = :tenantId');
            $query->setParameter('tenantId', $tenantId);
        } else {
            $query->innerJoin(
                'tenant.contracts',
                'contract'
            );
            $query->where('contract.holding = :holdingId');
            $query->setParameter('holdingId', $holdingId);
            $query->setMaxResults(1);
        }

        return $query;
    }

    public function getContractsByHoldingAndResident(ResidentMapping $residentMapping, Holding $landlordHolding)
    {
        $query = $this->createQueryBuilder('tenant');

        $query->innerJoin(
            'tenant.contracts',
            'contract'
        );

        $query->innerJoin(
            'tenant.residentsMapping',
            'resident'
        );

        $query->where('resident.holding = :holdingId');
        $query->andWhere('resident.residentId = :residentId');
        $query->setParameter('holdingId', $landlordHolding->getId());
        $query->setParameter('residentId', $residentMapping->getResidentId());

        return $query->getQuery()->execute();
    }

    /**
     * @param Holding $holding
     * @param string $residentId
     * @return null|Tenant
     * @throws NonUniqueResultException
     */
    public function getTenantWithPendingInvitationByHoldingAndResidentId(Holding $holding, $residentId)
    {
        return $this->createQueryBuilder('tenant')
            ->innerJoin(
                'tenant.residentsMapping',
                'resident'
            )
            ->where('resident.holding = :holding')
            ->andWhere('resident.residentId = :residentId')
            ->andWhere('tenant.invite_code IS NOT NULL')
            ->setParameter('holding', $holding)
            ->setParameter('residentId', $residentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $leaseId
     * @param string $unitId
     * @return Tenant|null
     * @throws NonUniqueResultException
     */
    public function getTenantByLeaseIdorUnitId($leaseId = null, $unitId = null)
    {
        if (empty($leaseId) && empty($unitId)) {
            return null;
        }

        $query = $this->createQueryBuilder('tenant')
            ->innerJoin(
                'tenant.contracts',
                'contracts'
            );

        if (!empty($leaseId)) {
            $query->andWhere('contracts.externalLeaseId = :leaseId')
                ->setParameter('leaseId', $leaseId);
        }

        if (!empty($unitId)) {
            $query->innerJoin('contracts.unit', 'unit')
                ->leftJoin('unit.unitMapping', 'unitMapping')
                ->andWhere('unitMapping.externalUnitId = :unitId')
                ->setParameter('unitId', $unitId);
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}
