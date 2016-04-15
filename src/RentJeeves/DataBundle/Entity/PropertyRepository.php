<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\DataBundle\Enum\AccountingSystem;

/**
 * @method Property find($id, $lockMode = LockMode::NONE, $lockVersion = null)
 */
class PropertyRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function getDuplicateProperties()
    {
        return $this->createQueryBuilder('property')
            ->select(
                '
                    property.id,
                    propertyAddress.zip,
                    propertyAddress.number,
                    propertyAddress.street,
                    COUNT(propertyAddress.street) AS street_c,
                    COUNT(propertyAddress.number) AS number_c,
                    COUNT(propertyAddress.zip) AS zip_c
                    '
            )
            ->innerJoin('property.propertyAddress', 'propertyAddress')
            ->groupBy(
                'propertyAddress.street',
                'propertyAddress.number',
                'propertyAddress.zip'
            )
            ->having(
                'street_c > 1
                    AND number_c > 1
                    AND zip_c > 1'
            )
            ->getQuery()
            ->execute();
    }

    /**
     * @return array
     */
    public function getDublicatePropertiesWithContract()
    {
        $sql = <<< EOT
SELECT (
COUNT( property.id ) - COUNT(DISTINCT(property.id))) AS difference,
property.id AS property_id, propertyAddress.zip AS zip, propertyAddress.number AS number,
propertyAddress.street AS street, contract.id AS contract_id,
COUNT( contract.id ) AS count_contract, COUNT( propertyAddress.zip ) AS count_zip,
COUNT( propertyAddress.number ) AS count_number, COUNT( propertyAddress.street ) AS count_street
FROM rj_property as property
INNER JOIN rj_contract as contract ON property.id = contract.property_id
INNER JOIN rj_property_address as propertyAddress ON property.property_address_id = propertyAddress.id
GROUP BY propertyAddress.street, propertyAddress.number, propertyAddress.zip
HAVING count_street > 1
AND count_number > 1
AND count_zip > 1
AND difference = 0

EOT;
        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param Group $group
     *
     * @return Property[]
     */
    public function getAllPropertiesInGroup(Group $group)
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.property_groups', 'g')
            ->where('g.id = :group_id')
            ->setParameter('group_id', $group->getId())
            ->getQuery();

        return $query->execute();
    }

    /**
     * @param Group $group
     *
     * @return Property[]
     */
    public function getAllPropertiesInGroupOrderedByAddress(Group $group)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('CONCAT(propertyAddress.number, propertyAddress.street) AS HIDDEN sortField')
            ->innerJoin('p.propertyAddress', 'propertyAddress')
            ->innerJoin('p.property_groups', 'g')
            ->where('g.id = :group_id')
            ->setParameter('group_id', $group->getId())
            ->orderBy('sortField')
            ->getQuery()
            ->execute();
    }

    /**
     * @param Group $group
     * @param string $searchBy
     * @param string $search
     *
     * @return mixed
     */
    public function countProperties($group, $searchBy = 'street', $search = '')
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.propertyAddress', 'propertyAddress')
            ->innerJoin('p.property_groups', 'g')
            ->where('g.id = :group_id')
            ->setParameter('group_id', $group->getId());
        if (!empty($search)) {
            $searchBy = $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy . ' LIKE :search');
                $query->setParameter('search', '%' . $item . '%');
            }
        }
        $query = $query->getQuery();

        return $query->getScalarResult();
    }

    public function getPropetiesPage(
        $group,
        $page = 1,
        $limit = 100,
        $sort = 'number',
        $isSortAsc = true,
        $searchBy = 'street',
        $search = ''
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.propertyAddress', 'propertyAddress');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        if (!empty($search)) {
            $searchBy = $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy . ' LIKE :search');
                $query->setParameter('search', '%' . $item . '%');
            }
        }
        if ($isSortAsc) {
            $order = 'ASC';
        } else {
            $order = 'DESC';
        }
        $query->orderBy('propertyAddress.' . $sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param string $searchBy
     *
     * @return string $searchBy
     */
    private function applySearchField($searchBy)
    {
        switch ($searchBy) {
            case 'street':
                $searchBy = 'CONCAT(propertyAddress.street, propertyAddress.number)';
                break;
            default:
                $searchBy = 'propertyAddress.' . $searchBy;
        }

        return $searchBy;
    }

    /**
     * @param string $search
     *
     * @return array
     */
    private function prepareSearch($search)
    {
        $search = preg_replace('/\s+/', ' ', trim($search));

        return explode(' ', $search);
    }

    /**
     * @param int $propertyId
     *
     * @return int
     */
    public function countGroup($propertyId)
    {
        return $this->createQueryBuilder('p')
            ->select('count(g.id)')
            ->innerJoin('p.property_groups', 'g')
            ->where('p.id = :propertyId')
            ->setParameter('propertyId', $propertyId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $propertyId
     *
     * @return mixed
     */
    public function findOneWithUnitAndAlphaNumericSort($propertyId)
    {
        $result = $this->createQueryBuilder('p')
            ->select('LENGTH(unit.name) as co,p,unit')
            ->leftJoin('p.units', 'unit')
            ->where('p.id = :propertyId')
            ->setParameter('propertyId', $propertyId)
            ->addOrderBy('co', 'ASC')
            ->addOrderBy('unit.name', 'ASC')
            ->getQuery()
            ->getResult();

        if (isset($result[0][0])) {
            return $result[0][0];
        }

        return null;
    }

    /**
     * @param Holding $holding
     *
     * @return array
     */
    public function findByHoldingAndAlphaNumericSort(Holding $holding)
    {
        $result = $this->createQueryBuilder('p')
            ->innerJoin('p.propertyAddress', 'propertyAddress')
            ->select('LENGTH(unit.name) as co,p,unit')
            ->innerJoin('p.property_groups', 'p_group')
            ->leftJoin('p.units', 'unit')
            ->where('p_group.holding_id = :holdingId')
            ->andWhere('unit.holding = :holdingId')
            ->andWhere('propertyAddress.lat IS NOT NULL AND propertyAddress.long IS NOT NULL')
            ->setParameter('holdingId', $holding->getId())
            ->addOrderBy('co', 'ASC')
            ->addOrderBy('unit.name', 'ASC')
            ->getQuery()
            ->getResult();

        if (!empty($result)) {
            $result = array_map('current', $result);
        }

        return $result;
    }

    /**
     * @param Holding $holding
     *
     * @return Property[]
     */
    public function findByHoldingOrderedByAddress(Holding $holding)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.propertyAddress', 'propertyAddress')
            ->addSelect('CONCAT(propertyAddress.number, propertyAddress.street) AS HIDDEN sortField')
            ->innerJoin('p.property_groups', 'p_group')
            ->leftJoin('p.units', 'unit')
            ->where('p_group.holding_id = :holdingId')
            ->andWhere('unit.holding = :holdingId')
            ->andWhere('propertyAddress.lat IS NOT NULL AND propertyAddress.long IS NOT NULL')
            ->setParameter('holdingId', $holding->getId())
            ->orderBy('sortField')
            ->getQuery()
            ->execute();
    }

    /**
     * @param Holding $holding
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findByHolding(Holding $holding = null)
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.propertyAddress', 'propertyAddress')
            ->innerJoin('p.property_groups', 'p_group')
            ->where('propertyAddress.lat IS NOT NULL AND propertyAddress.long IS NOT NULL');
        if ($holding) {
            $query
                ->andWhere('p_group.holding_id = :holdingId')
                ->setParameter('holdingId', $holding->getId());
        }

        $query
            ->addOrderBy('propertyAddress.street', 'ASC')
            ->addOrderBy('propertyAddress.number', 'ASC');

        return $query;
    }

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     *
     * @return Property|null
     * @throws NonUniqueResultException
     */
    public function getPropertiesByExternalId(Holding $holding, $externalPropertyId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.propertyMappings', 'pm')
            ->where('pm.holding = :holdingId')
            ->andWhere('pm.externalPropertyId = :externalPropertyId')
            ->setParameter('holdingId', $holding->getId())
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Group  $group
     * @param string $externalPropertyId
     *
     * @return Property[]
     */
    public function findAllByGroupAndExternalId(Group $group, $externalPropertyId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.propertyMappings', 'pm')
            ->innerJoin('p.property_groups', 'pg')
            ->where('pm.holding = :holdingId')
            ->andWhere('pm.externalPropertyId = :externalPropertyId')
            ->andWhere('pg.id = :groupId')
            ->setParameter('holdingId', $group->getHolding()->getId())
            ->setParameter('groupId', $group->getId())
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param Address $address
     *
     * @return Property
     */
    public function findOneByAddress(Address $address)
    {
        if ($address->getIndex() === null) {
            throw new \LogicException('Address doesn`t have data about location');
        }

        return $this->createQueryBuilder('p')
            ->innerJoin('p.propertyAddress', 'propertyAddress')
            ->where('propertyAddress.index = :index')
            ->setParameter('index', $address->getIndex())
            ->andWhere('propertyAddress.number = :number')
            ->setParameter('number', $address->getNumber())
            ->setMaxResults(1) /** @TODO: remove this after adding unique index for field `ss_index` */
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $criteria
     *
     * @return Property|null
     */
    public function findOneByPropertyAddressFields(array $criteria)
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.propertyAddress', 'propertyAddress');

        foreach ($criteria as $field => $value) {
            $query
                ->andWhere(sprintf('propertyAddress.%s = :%s', $field, $field))
                ->setParameter($field, $value);
        }

        /** TODO: change this to oneOrNull once duplicate properties removed from DB */

        return $query->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Property $property
     *
     * @return Property[]
     */
    public function findAllOtherPropertiesWithSamePropertyAddress(Property $property)
    {
        return $this->createQueryBuilder('p')
            ->where('p.propertyAddress = :propertyAddress')
            ->andWhere('p.id != :currentPropertyId')
            ->setParameter('propertyAddress', $property->getPropertyAddress())
            ->setParameter('currentPropertyId', $property->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * @param  string $accountingSystem
     * @param  string $externalPropertyId
     * @param  string $externalUnitId
     * @param  string $externalBuildingId
     * @param  string|null $holdingId
     * @return null|Property
     * @throws NonUniqueResultException
     */
    public function getPropertyByExternalPropertyUnitIds(
        $accountingSystem,
        $externalPropertyId,
        $externalUnitId,
        $externalBuildingId = null,
        $holdingId = null
    ) {
        AccountingSystem::throwsInvalid($accountingSystem);

        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.propertyMappings', 'pm')
            ->innerJoin('p.units', 'units')
            ->innerJoin('units.unitMapping', 'um')
            ->innerJoin('pm.holding', 'h')
            ->andWhere('units.holding = pm.holding')
            ->andWhere('h.accountingSystem = :accountingSystem')
            ->andWhere('pm.externalPropertyId = :externalPropertyId')
            ->andWhere('um.externalUnitId LIKE :externalUnitMask')
            ->setParameter('accountingSystem', $accountingSystem)
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->setParameter(
                'externalUnitMask',
                sprintf(
                    '%s|%s|%s',
                    $externalPropertyId,
                    $externalBuildingId ?: '%',
                    $externalUnitId
                )
            );

        if ($holdingId) {
            $query
                ->andWhere('h.id = :holdingId')
                ->setParameter('holdingId', $holdingId);
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param  string $accountingSystem
     * @param  string $externalPropertyId
     * @param  string|null $holdingId
     * @return null|Property
     * @throws NonUniqueResultException
     */
    public function getPropertyByExternalPropertyId($accountingSystem, $externalPropertyId, $holdingId = null)
    {
        AccountingSystem::throwsInvalid($accountingSystem);

        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.propertyMappings', 'pm')
            ->innerJoin('pm.holding', 'h')
            ->andWhere('h.accountingSystem = :accountingSystem')
            ->andWhere('pm.externalPropertyId = :externalPropertyId')
            ->setParameter('accountingSystem', $accountingSystem)
            ->setParameter('externalPropertyId', $externalPropertyId);

        if ($holdingId) {
            $query
                ->andWhere('h.id = :holdingId')
                ->setParameter('holdingId', $holdingId);
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param  string $accountingSystem
     * @param  string $externalPropertyId
     * @param  string|null $holdingId
     * @return Property[]
     */
    public function getPropertiesByExternalPropertyId($accountingSystem, $externalPropertyId, $holdingId = null)
    {
        AccountingSystem::throwsInvalid($accountingSystem);

         $query = $this->createQueryBuilder('p')
            ->innerJoin('p.propertyMappings', 'pm')
            ->innerJoin('pm.holding', 'h')
            ->andWhere('h.accountingSystem = :accountingSystem')
            ->andWhere('pm.externalPropertyId = :externalPropertyId')
            ->setParameter('accountingSystem', $accountingSystem)
            ->setParameter('externalPropertyId', $externalPropertyId);
        if ($holdingId) {
            $query
                ->andWhere('h.id = :holdingId')
                ->setParameter('holdingId', $holdingId);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param Property $property
     * @return boolean
     * @throws NonUniqueResultException|NoResultException
     */
    public function checkPropertyBelongOneGroup(Property $property)
    {
        if ($property->getPropertyGroups()->count() > 1) {
            throw new NonUniqueResultException('Property belongs to more then one group');
        }

        return (bool) $this->createQueryBuilder('p')
            ->select('1')
            ->innerJoin('p.units', 'u')
            ->where('p.id = :property')
            ->having('COUNT(DISTINCT u.group) = 1')
            ->setParameter('property', $property)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
