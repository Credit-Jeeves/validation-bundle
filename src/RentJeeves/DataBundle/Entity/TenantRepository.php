<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TenantRepository extends EntityRepository
{
    public function countTenants($group, $searchBy = 'address', $search = '')
    {
        $query = $this->createQueryBuilder('t');
        $query->innerJoin('t.contracts', 'c');
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
}
