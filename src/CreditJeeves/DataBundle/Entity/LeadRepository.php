<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LeadRepository extends EntityRepository
{
    /**
     *
     * @param \CreditJeeves\DataBundle\Entity\User $User
     */
    public function deleteUserLeads(\CreditJeeves\DataBundle\Entity\User $User)
    {
        $query = $this->createQueryBuilder('l')
                      ->delete()
                      ->where('l.cj_applicant_id = :id')
                      ->setParameter('id', $User->getId())
                      ->getQuery()
                      ->execute();
    }
}
