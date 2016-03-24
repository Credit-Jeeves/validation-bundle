<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ImportPropertyRepository extends EntityRepository
{
    /**
     * @param Import $import
     * @param string|null $externalPropertyId
     *
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getNotProcessedImportProperties(Import $import, $externalPropertyId = null)
    {
        $query = $this->createQueryBuilder('ip')
            ->where('ip.processed = 0')
            ->andWhere('ip.import = :import')
            ->setParameter('import', $import);

        if (!is_null($externalPropertyId)) {
            $query
                ->andWhere('ip.externalPropertyId = :extPropertyId')
                ->setParameter('extPropertyId', $externalPropertyId);
        }

        return $query
            ->getQuery()
            ->iterate();
    }
}
