<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtUnit;

/**
 * Service`s name "import.property.transformer.resman"
 */
class ResmanTransformer implements TransformerInterface
{
    /**
     * @var EntityManagerInterface
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
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     */
    final public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param array  $accountingSystemData
     * @param Import $import
     */
    public function transformData(array $accountingSystemData, Import $import)
    {
        $this->logger->info(
            sprintf(
                'Starting process transformData for Import#%d via ResmanTransformer.',
                $import->getId()
            ),
            ['group' => $import->getGroup()]
        );

        /** @var RtCustomer $accountingSystemRecord */
        foreach ($accountingSystemData as $accountingSystemRecord) {
            $rtUnit = $accountingSystemRecord->getRtUnit();
            $extUnitId = $this->getExternalUnitId($rtUnit);
            if (true === $this->checkExistImportPropertyInCache($import, $extUnitId)) {
                continue;
            }
            $importProperty = new ImportProperty();
            $importProperty->setImport($import);
            $importProperty->setExternalPropertyId($this->getExternalPropertyId($rtUnit));
            $importProperty->setExternalBuildingId($this->getExternalBuildingId($rtUnit));
            $importProperty->setAddressHasUnits($this->getAddressHasUnits($rtUnit));
            $importProperty->setPropertyHasBuildings($this->getPropertyHasBuildings($rtUnit));
            $importProperty->setUnitName($this->getUnitName($rtUnit));
            $importProperty->setExternalUnitId($extUnitId);
            $importProperty->setAddress1($this->getAddress1($rtUnit));
            $importProperty->setCity($this->getCity($rtUnit));
            $importProperty->setState($this->getState($rtUnit));
            $importProperty->setZip($this->getZip($rtUnit));
            $importProperty->setAllowMultipleProperties($this->getAllowMultipleProperties($rtUnit));

            $this->em->persist($importProperty);
            $this->arrayCache[] = $import->getId() . '|' . $extUnitId;
            $this->em->flush();
        }

        $this->logger->info(
            sprintf(
                'Finished process transformData for Import#%d',
                $import->getId()
            ),
            ['group' => $import->getGroup()]
        );
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getExternalPropertyId(RtUnit $rtUnit)
    {
        return $rtUnit->getUnit()->getPropertyPrimaryID();
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getExternalBuildingId(RtUnit $rtUnit)
    {
        return $rtUnit->getUnit()->getInformation()->getBuildingID();
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getAddressHasUnits(RtUnit $rtUnit)
    {
        return true;
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getPropertyHasBuildings(RtUnit $rtUnit)
    {
        return false;
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getUnitName(RtUnit $rtUnit)
    {
        return $rtUnit->getUnitId();
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getExternalUnitId(RtUnit $rtUnit)
    {
        return $rtUnit->getExternalUnitId();
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getAddress1(RtUnit $rtUnit)
    {
        return $rtUnit->getUnit()->getInformation()->getAddress()->getAddress1();
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getCity(RtUnit $rtUnit)
    {
        return $rtUnit->getUnit()->getInformation()->getAddress()->getCity();
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getState(RtUnit $rtUnit)
    {
        return $rtUnit->getUnit()->getInformation()->getAddress()->getState();
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getZip(RtUnit $rtUnit)
    {
        return $rtUnit->getUnit()->getInformation()->getAddress()->getPostalCode();
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return boolean
     */
    protected function getAllowMultipleProperties(RtUnit $rtUnit)
    {
        return false;
    }

    /**
     * @param Import $import
     * @param string $extUnitId
     *
     * @return bool
     */
    protected function checkExistImportPropertyInCache(Import $import, $extUnitId)
    {
        return in_array($import->getId() . '|' . $extUnitId, $this->arrayCache);
    }
}
