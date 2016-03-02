<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Occupant;

class AMSITransformer implements TransformerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $arrayCache = [];

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function transformData(array $accountingSystemData, Import $import)
    {
        $this->logger->info(
            sprintf(
                'Started transform data for Import#%d',
                $import->getId()
            ),
            ['group' => $import->getGroup()]
        );

        /** @var Lease $lease */
        foreach ($accountingSystemData as $lease) {
            $occupants = $lease->getOccupants();
            /** @var Occupant $occupant */
            foreach ($occupants as $occupant) {
                if ($this->checkExistImportPropertyInCache($import, $lease, $occupant) === true) {
                    continue;
                }
                $importProperty = new ImportProperty();
                $importProperty->setImport($import);
                $import->addImportProperty($importProperty);

                $importProperty->setExternalBuildingId($this->getExternalBuildingId($lease, $occupant));
                $importProperty->setAddressHasUnits($this->isAddressHasUnits($lease, $occupant));
                $importProperty->setPropertyHasBuildings($this->isPropertyHasBuildings($lease, $occupant));
                $importProperty->setExternalPropertyId($this->getExternalPropertyId($lease, $occupant));
                $importProperty->setUnitName($this->getUnitName($lease, $occupant));
                $importProperty->setExternalUnitId($this->getExternalUnitId($lease, $occupant));
                $importProperty->setAddress1($this->getAddress1($lease, $occupant));
                $importProperty->setCity($this->getCity($lease, $occupant));
                $importProperty->setState($this->getState($lease, $occupant));
                $importProperty->setZip($this->getZip($lease, $occupant));
                $importProperty->setAllowMultipleProperties($this->isAllowedMultipleProperties($lease, $occupant));

                $this->em->persist($importProperty);

                $this->arrayCache[] = $this->getUniqueCacheKey($import, $lease, $occupant);
            }
        }

        $this->em->flush();

        $this->logger->info(
            sprintf(
                'Finished transform data for Import#%d',
                $import->getId()
            ),
            ['group' => $import->getGroup()]
        );
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return bool
     */
    protected function isAllowedMultipleProperties(Lease $lease, Occupant $occupant)
    {
        return true;
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return string
     */
    protected function getExternalBuildingId(Lease $lease, Occupant $occupant)
    {
        return $lease->getBldgId();
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return bool
     */
    protected function isPropertyHasBuildings(Lease $lease, Occupant $occupant)
    {
        return true;
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return bool
     */
    protected function isAddressHasUnits(Lease $lease, Occupant $occupant)
    {
        return true;
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return string
     */
    protected function getUnitName(Lease $lease, Occupant $occupant)
    {
        return $occupant->getUnitId();
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return string
     */
    protected function getExternalUnitId(Lease $lease, Occupant $occupant)
    {
        return $lease->getExternalUnitId();
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return string
     */
    protected function getAddress1(Lease $lease, Occupant $occupant)
    {
        return $lease->getUnit()->getAddress1();
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return string
     */
    protected function getCity(Lease $lease, Occupant $occupant)
    {
        return $lease->getUnit()->getCity();
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return string
     */
    protected function getState(Lease $lease, Occupant $occupant)
    {
        return $lease->getUnit()->getState();
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return string
     */
    protected function getZip(Lease $lease, Occupant $occupant)
    {
        return $lease->getUnit()->getZip();
    }

    /**
     * @param Import $import
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return bool
     */
    protected function checkExistImportPropertyInCache(Import $import, Lease $lease, Occupant $occupant)
    {
        return in_array(
            $this->getUniqueCacheKey($import, $lease, $occupant),
            $this->arrayCache
        );
    }

    /**
     * @param Lease $lease
     * @param Occupant $occupant
     *
     * @return string
     */
    protected function getExternalPropertyId(Lease $lease, Occupant $occupant)
    {
        return $lease->getPropertyId();
    }

    /**
     * @param Import $import
     * @param Lease $lease
     * @return string
     */
    protected function getUniqueCacheKey(Import $import, Lease $lease, Occupant $occupant)
    {
        return sprintf('%s|%s', $import->getId(), $this->getExternalUnitId($lease, $occupant));
    }
}
