<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class OrderRepository extends EntityRepository
{
    /**
     *
     * @param \CreditJeeves\DataBundle\Entity\User $User
     */
    public function deleteUserOrders(\CreditJeeves\DataBundle\Entity\User $User)
    {
        $query = $this->createQueryBuilder('o')
                      ->delete()
                      ->where('o.cj_applicant_id = :id')
                      ->setParameter('id', $User->getId())
                      ->getQuery()
                      ->execute();
    }
}
