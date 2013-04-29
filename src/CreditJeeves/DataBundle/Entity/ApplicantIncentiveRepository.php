<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class ApplicantIncentiveRepository extends EntityRepository
{
    /**
     * 
     */
    public function listIncentivesByUser($User)
    {
        $query = $this->createQueryBuilder('i')
            ->where('i.cj_applicant_id = :nUserId')
            ->setParameter('nUserId', $User->getId())
            ->getQuery();
        return $query->execute();
    }
}
