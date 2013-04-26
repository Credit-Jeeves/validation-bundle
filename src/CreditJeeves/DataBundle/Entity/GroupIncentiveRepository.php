<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class GroupIncentiveRepository extends EntityRepository
{
    /**
     * 
     * @param integer $nGroupId
     */
    public function listIncentivesByGroupId($nGroupId)
    {
        $query = $this->createQueryBuilder('i')
            ->where('i.cj_group_id = :nGroupId')
            ->setParameter('nGroupId', $nGroupId)
            ->orderBy('i.consecutive_number', 'ASC')
            ->getQuery();
        return $query->execute();
    }
}
