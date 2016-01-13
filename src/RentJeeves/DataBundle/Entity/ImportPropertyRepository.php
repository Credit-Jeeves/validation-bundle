<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ImportPropertyRepository extends EntityRepository
{
    /**
     * @param Import $import
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getNotProcessedImportProperties(Import $import)
    {
        return $this->createQueryBuilder('ip')
            ->where('ip.processed = 0')
            ->andWhere('ip.import = :import')
            ->setParameter('import', $import)
            ->getQuery()
            ->iterate();
    }
}
