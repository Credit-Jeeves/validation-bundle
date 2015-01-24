<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Doctrine\ORM\Query\Expr;

class TenantRepository extends EntityRepository
{
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

    public function getTenantForImportWithResident($email, $residentId, $holdingId)
    {
        $query = $this->createQueryBuilder('tenant');
        if (!empty($residentId)) {
            $query->leftJoin(
                'tenant.residentsMapping',
                'resident'
            );
        }

        //Priority have inside table https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
        if (!empty($email) && !empty($residentId)) {
            $query->where('tenant.email = :email');
            $query->orWhere('resident.residentId = :residentId AND resident.holding = :holdingId');
            $query->setParameter('residentId', $residentId);
            $query->setParameter('holdingId', $holdingId);
            $query->setParameter('email', $email);
        } elseif (!empty($residentId)) {
            $query->where('resident.residentId = :residentId');
            $query->andWhere('resident.holding = :holdingId');
            $query->setParameter('residentId', $residentId);
            $query->setParameter('holdingId', $holdingId);
        } elseif (!empty($email)) {
            $query->where('tenant.email = :email');
            $query->setParameter('email', $email);
        } else {
            return;
        }

        $query->setMaxResults(1);
        $query = $query->getQuery();

        $result = $query->getResult();

        return reset($result);
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
}
