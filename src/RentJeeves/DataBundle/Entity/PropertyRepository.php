<?php
namespace RentJeeves\DataBundle\Entity;

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
        SELECT (COUNT(property.id) - count(distinct(property.id))) as difference,
        property.id AS property_id, property.zip AS zip,
        property.number AS number, property.street AS street,
        contract.id as contract_id,
        COUNT(contract.id) as count_contract
        FROM rj_property property
        INNER JOIN rj_contract contract
        ON property.id = contract.property_id
        GROUP BY property.street, property.number, property.zip
        HAVING difference = 0

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

    public function findOneWithUnitAndAlphaNumericSort($propertyId, $holdingId = null)
    {
        $query = $this->createQueryBuilder('p')
                      ->select('LENGTH(u.name) as co,p,u');
        $query->leftJoin('p.units', 'u');
        $query->where('p.id = :propertyId');
        if ($holdingId) {
            $query->leftJoin('p.property_groups', 'group');
            $query->andWhere('group.holding_id = :holdingId');
            $query->setParameter('holdingId', $holdingId);
        }
        $query->setParameter('propertyId', $propertyId);
        $query->addOrderBy('co', 'ASC');
        $query->addOrderBy('u.name', 'ASC');
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
}
