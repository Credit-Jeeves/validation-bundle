<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;

class ResidentMappingRepository extends EntityRepository
{
    public function checkResidentMappingDuplicate(Holding $holding, $residentId, $email)
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

    /**
     * @param Holding $holding
     * @param string  $residentId
     *
     * @return ResidentMapping|null
     */
    public function findOneResidentByHoldingAndResidentId(Holding $holding, $residentId)
    {
        return $this->createQueryBuilder('mp')
            ->where('mp.holding = :holding')
            ->andWhere('mp.residentId = :residentId')
            ->setParameter('holding', $holding)
            ->setParameter('residentId', $residentId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
