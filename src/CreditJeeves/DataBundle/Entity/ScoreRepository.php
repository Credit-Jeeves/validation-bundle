<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class ScoreRepository extends EntityRepository
{
    /**
     * 
     * @param \CreditJeeves\DataBundle\Entity\User $User
     */
    public function deleteUserScores(\CreditJeeves\DataBundle\Entity\User $User)
    {
        $query = $this->createQueryBuilder('s')
                      ->delete()
                      ->where('s.cj_applicant_id = :id')
                      ->setParameter('id', $User->getId())
                      ->getQuery()
                      ->execute();
    }
}