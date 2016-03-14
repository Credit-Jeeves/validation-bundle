<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;

/**
 * Service`s name "import.property.transformer.yardi"
 */
class YardiTransformer implements TransformerInterface
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

        /** @var FullResident $residentTransactionServiceTransactions */
        foreach ($accountingSystemData as $fullResident) {
            if ($this->checkExistImportPropertyInCache($import, $fullResident) === true) {
                continue;
            }
            $importProperty = new ImportProperty();
            $importProperty->setImport($import);
            $import->addImportProperty($importProperty);

            $importProperty->setExternalBuildingId($this->getExternalBuildingId($fullResident));
            $importProperty->setAddressHasUnits($this->isAddressHasUnits($fullResident));
            $importProperty->setPropertyHasBuildings($this->isPropertyHasBuildings($fullResident));
            $importProperty->setExternalPropertyId($this->getExternalPropertyId($fullResident));
            $importProperty->setUnitName($this->getUnitName($fullResident));
            $importProperty->setExternalUnitId($this->getExternalUnitId($fullResident));
            $importProperty->setAddress1($this->getAddress1($fullResident));
            $importProperty->setCity($this->getCity($fullResident));
            $importProperty->setState($this->getState($fullResident));
            $importProperty->setZip($this->getZip($fullResident));
            $importProperty->setAllowMultipleProperties($this->isAllowedMultipleProperties($fullResident));

            $this->em->persist($importProperty);

            $this->arrayCache[] = $this->getUniqueCacheKey($import, $fullResident);
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
     * @param FullResident $accountingSystemRecord
     *
     * @return bool
     */
    public function isAllowedMultipleProperties(FullResident $fullResident)
    {
        return true;
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return null
     */
    public function getExternalBuildingId(FullResident $fullResident)
    {
        return null;
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return bool
     */
    protected function isPropertyHasBuildings(FullResident $fullResident)
    {
        return false;
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return bool
     */
    protected function isAddressHasUnits(FullResident $fullResident)
    {
        return true;
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalPropertyId(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getCode();
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getResidentData()->getUnit()->getIdentification()->getUnitName();
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalUnitId(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getExternalUnitId($this->getUnitName($accountingSystemRecord));
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getAddressLine1();
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getCity(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getCity();
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getState(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getState();
    }

    /**
     * @param FullResident $accountingSystemRecord
     *
     * @return string
     */
    protected function getZip(FullResident $accountingSystemRecord)
    {
        return $accountingSystemRecord->getProperty()->getPostalCode();
    }

    /**
     * @param Import $import
     * @param FullResident  $accountingSystemRecord
     *
     * @return bool
     */
    protected function checkExistImportPropertyInCache(Import $import, FullResident $accountingSystemRecord)
    {
        return in_array(
            $this->getUniqueCacheKey($import, $accountingSystemRecord),
            $this->arrayCache
        );
    }

    /**
     * @param Import $import
     * @param FullResident $accountingSystemRecord
     * @return string
     */
    protected function getUniqueCacheKey(Import $import, FullResident $accountingSystemRecord)
    {
        return sprintf('%s|%s', $import->getId(), $this->getExternalUnitId($accountingSystemRecord));
    }
}
