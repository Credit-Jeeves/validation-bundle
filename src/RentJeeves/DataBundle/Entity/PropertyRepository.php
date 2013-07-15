<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PropertyRepository extends EntityRepository
{
    public function getPropetiesList($group, $page = 1, $limit = 2)
    {
//         $query = $this->createQueryBuilder('p');
//         $query->innerJoin('p.property_groups', 'g');
//         $query->setParameter('g.id', $group->getId());
//         $query = $query->getQuery();
//         return $query->execute();
        
        
        $offset = ($page - 1) * $limit;
        return $this->getEntityManager()
            ->createQuery(
                "SELECT 
                    p
                 FROM 
                    RjDataBundle:Property p
                 INNER JOIN 
                    p.property_groups g
                 WHERE
                    g.id = {$group->getId()}
                 ORDER BY 
                    p.street ASC"
            )
        ->getResult();
    }
}
