<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;

/**
 * Class ResidentMappingRepository
 * @package RentJeeves\DataBundle\Entity
 */
class ResidentMappingRepository extends EntityRepository
{
    public function checkDuplicate(Holding $holding, $residentId, $email)
    {
        $query = $this->createQueryBuilder('mapping');
        $query->innerJoin('mapping.tenant', 'tenant');
        $query->where(
            'tenant.email = :email AND
            mapping.residentId = :residentId AND
            mapping.holding = :holdingId
            '
        );

        $query->setParameter('email', $email);
        $query->setParameter('residentId', $residentId);
        $query->setParameter('holdingId', $holding->getId());

        $query = $query->getQuery();

        return $query->execute();
    }
}
