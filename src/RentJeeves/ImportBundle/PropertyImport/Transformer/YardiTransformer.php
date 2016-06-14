<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;

/**
 * Service`s name "import.property.transformer.yardi"
 */
class YardiTransformer implements TransformerInterface
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

        $countryFromSettings = $import->getGroup()->getGroupSettings()->getCountryCode();
        /** @var UnitInformation $unitInformation */
        foreach ($accountingSystemData as $unitInformation) {
            if ($this->checkExistImportPropertyInCache($import, $unitInformation) === true) {
                continue;
            }
            $importProperty = new ImportProperty();
            $importProperty->setImport($import);
            $import->addImportProperty($importProperty);

            $importProperty->setExternalBuildingId($this->getExternalBuildingId($unitInformation));
            $importProperty->setAddressHasUnits($this->isAddressHasUnits($unitInformation));
            $importProperty->setPropertyHasBuildings($this->isPropertyHasBuildings($unitInformation));
            $importProperty->setExternalPropertyId($this->getExternalPropertyId($unitInformation));
            $importProperty->setUnitName($this->getUnitName($unitInformation));
            $importProperty->setExternalUnitId($this->getExternalUnitId($unitInformation));
            $importProperty->setAddress1($this->getAddress1($unitInformation));
            $importProperty->setCity($this->getCity($unitInformation));
            $importProperty->setState($this->getState($unitInformation));
            $importProperty->setZip($this->getZip($unitInformation));
            $importProperty->setCountry($this->getCountry($unitInformation, $countryFromSettings));
            $importProperty->setAllowMultipleProperties($this->isAllowedMultipleProperties($unitInformation));

            $this->em->persist($importProperty);

            $this->arrayCache[] = $this->getUniqueCacheKey($import, $unitInformation);
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
     * @param UnitInformation $accountingSystemRecord
     *
     * @return bool
     */
    public function isAllowedMultipleProperties(UnitInformation $accountingSystemRecord)
    {
        return true;
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return null
     */
    public function getExternalBuildingId(UnitInformation $accountingSystemRecord)
    {
        return null;
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return bool
     */
    protected function isPropertyHasBuildings(UnitInformation $accountingSystemRecord)
    {
        return false;
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return bool
     */
    protected function isAddressHasUnits(UnitInformation $accountingSystemRecord)
    {
        return true;
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalPropertyId(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getCode();
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getUnit()->getUnitId();
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalUnitId(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getExternalUnitId($this->getUnitName($accountingSystemRecord));
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getAddressLine1();
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getCity(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getCity();
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getState(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getState();
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getZip(UnitInformation $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getPostalCode();
    }

    /**
     * @param UnitInformation $accountingSystemRecord
     * @param string          $countryFromSettings
     *
     * @return string
     */
    protected function getCountry(UnitInformation $accountingSystemRecord, $countryFromSettings)
    {
        return $countryFromSettings;
    }

    /**
     * @param Import          $import
     * @param UnitInformation $accountingSystemRecord
     *
     * @return bool
     */
    protected function checkExistImportPropertyInCache(Import $import, UnitInformation $accountingSystemRecord)
    {
        return in_array(
            $this->getUniqueCacheKey($import, $accountingSystemRecord),
            $this->arrayCache
        );
    }

    /**
     * @param Import          $import
     * @param UnitInformation $accountingSystemRecord
     *
     * @return string
     */
    protected function getUniqueCacheKey(Import $import, UnitInformation $accountingSystemRecord)
    {
        return sprintf('%s|%s', $import->getId(), $this->getExternalUnitId($accountingSystemRecord));
    }
}
