<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;

class PropertyRepository extends EntityRepository
{
    public function getDuplicateProperties()
    {
        $query = $this->createQueryBuilder('property')
                ->select(
                    '
                    property.id,
                    property.zip,
                    property.number,
                    property.street,
                    COUNT(property.street) AS street_c,
                    COUNT(property.number) AS number_c,
                    COUNT(property.zip) AS zip_c
                    '
                )
                ->groupBy(
                    'property.street',
                    'property.number',
                    'property.zip'
                )
                ->having(
                    'street_c > 1
                    AND number_c > 1
                    AND zip_c > 1'
                );

        $query = $query->getQuery();

        return $query->execute();
    }

    public function getDublicatePropertiesWithContract()
    {
        $sql = <<< EOT
SELECT (
COUNT( property.id ) - COUNT(DISTINCT(property.id))) AS difference,
property.id AS property_id, property.zip AS zip, property.number AS number,
property.street AS street, contract.id AS contract_id,
COUNT( contract.id ) AS count_contract, COUNT( property.zip ) AS count_zip,
COUNT( property.number ) AS count_number, COUNT( property.street ) AS count_street
FROM rj_property as property
INNER JOIN rj_contract as contract ON property.id = contract.property_id
GROUP BY property.street, property.number, property.zip
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


    public function getPropetiesAll($group)
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * 
     * @param unknown_type $group
     * @param string $searchBy
     * @param string $search
     */
    public function countProperties($group, $searchBy = 'street', $search = '')
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        if (!empty($search)) {
            $searchBy = $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy.' LIKE :search');
                $query->setParameter('search', '%'.$item.'%');
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
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        if (!empty($search)) {
            $searchBy = $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy.' LIKE :search');
                $query->setParameter('search', '%'.$item.'%');
            }
        }
        if ($isSortAsc) {
            $order = 'ASC';
        } else {
            $order = 'DESC';
        }
        $query->orderBy('p.'.$sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }

    /**
     * 
     * @param unknown_type $searchBy
     */
    private function applySearchField($searchBy)
    {
        switch ($searchBy) {
            case 'street':
                $searchBy = 'CONCAT(p.street, p.number)';
                break;
            default:
                $searchBy = 'p.'.$searchBy;
        }
        return $searchBy;
    }

    /**
     * @param string $search
     * @return array
     */
    private function prepareSearch($search)
    {
        $search = preg_replace('/\s+/', ' ', trim($search));
        $search = explode(' ', $search);
        return $search;
    }

    public function countGroup($propertyId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('count(g.id)')
            ->innerJoin('p.property_groups', 'g')
            ->where('p.id = :propertyId')
            ->setParameter('propertyId', $propertyId);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    public function findOneWithUnitAndAlphaNumericSort($propertyId)
    {
        $query = $this->createQueryBuilder('p')
                      ->select('LENGTH(unit.name) as co,p,unit');
        $query->leftJoin('p.units', 'unit');
        $query->where('p.id = :propertyId');
        $query->setParameter('propertyId', $propertyId);
        $query->addOrderBy('co', 'ASC');
        $query->addOrderBy('unit.name', 'ASC');
        $query = $query->getQuery();
        $result = $query->getResult();

        if (isset($result[0][0])) {
            return $result[0][0];
        }
        return null;
    }

    public function findOneByJbKbWithUnitAndAlphaNumericSort($jb, $kb)
    {
        $query = $this->createQueryBuilder('p')
            ->select('LENGTH(u.name) as co,p,u');
        $query->leftJoin('p.units', 'u');
        $query->where('p.jb = :jb');
        $query->andWhere('p.kb = :kb');
        $query->setParameter('jb', $jb);
        $query->setParameter('kb', $kb);
        $query->addOrderBy('co', 'ASC');
        $query->addOrderBy('u.name', 'ASC');
        $query = $query->getQuery();
        $result = $query->getResult();

        if (isset($result[0][0])) {
            return $result[0][0];
        }

        return null;
    }

    public function findByHoldingAndAlphaNumericSort(Holding $holding)
    {
        $query = $this->createQueryBuilder('p')
            ->select('LENGTH(unit.name) as co,p,unit');
        $query->innerJoin('p.property_groups', 'p_group');
        $query->leftJoin('p.units', 'unit');
        $query->where('p_group.holding_id = :holdingId');
        $query->andWhere('unit.holding = :holdingId');
        $query->andWhere('p.jb IS NOT NULL AND p.kb IS NOT NULL');
        $query->setParameter('holdingId', $holding->getId());
        $query->addOrderBy('co', 'ASC');
        $query->addOrderBy('unit.name', 'ASC');
        $query = $query->getQuery();
        $result = $query->getResult();

        if (!empty($result)) {
            $result = array_map('current', $result);
        }

        return $result;
    }

    public function findByHolding(Holding $holding = null)
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'p_group');
        $query->where('p.jb IS NOT NULL AND p.kb IS NOT NULL');
        if ($holding) {
            $query->andWhere('p_group.holding_id = :holdingId');
            $query->setParameter('holdingId', $holding->getId());
        }
        $query->addOrderBy('p.street', 'ASC');
        $query->addOrderBy('p.number', 'ASC');

        return $query;
    }
}
