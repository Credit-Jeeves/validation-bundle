<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PropertyRepository extends EntityRepository
{
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
                      ->select('LENGTH(u.name) as co,p,u');
        $query->leftJoin('p.units', 'u');
        $query->where('p.id = :propertyId');
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
