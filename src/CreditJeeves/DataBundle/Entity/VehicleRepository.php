<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class VehicleRepository extends EntityRepository
{
    /**
     *
     * @param \CreditJeeves\DataBundle\Entity\User $User
     */
    public function deleteUserVehicles(\CreditJeeves\DataBundle\Entity\User $User)
    {
        $query = $this->createQueryBuilder('r')
                      ->delete()
                      ->where('r.cj_applicant_id = :id')
                      ->setParameter('id', $User->getId())
                      ->getQuery()
                      ->execute();
    }
}