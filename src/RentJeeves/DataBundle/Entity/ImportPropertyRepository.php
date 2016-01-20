<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ImportPropertyRepository extends EntityRepository
{
    /**
     * @param Import $import
     * @param string $externalPropertyId
     *
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getNotProcessedImportProperties(Import $import, $externalPropertyId)
    {
        return $this->createQueryBuilder('ip')
            ->where('ip.processed = 0')
            ->andWhere('ip.import = :import')
            ->andWhere('ip.externalPropertyId = :extPropertyId')
            ->setParameter('import', $import)
            ->setParameter('extPropertyId', $externalPropertyId)
            ->getQuery()
            ->iterate();
    }
}
