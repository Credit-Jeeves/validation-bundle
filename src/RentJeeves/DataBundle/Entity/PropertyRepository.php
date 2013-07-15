<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PropertyRepository extends EntityRepository
{
    public function getPropertiesByGroup($group)
    {
        $query = $this->createQueryBuilder('p')
            ->where('i.cj_applicant_id = :nUserId')
            ->setParameter('nUserId', $group->getId())
            ->getQuery();
        return $query->execute();
    }
}
