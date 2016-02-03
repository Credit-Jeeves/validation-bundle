<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;

/**
 * Service`s name "import.property.transformer.mri"
 */
class MRITransformer implements TransformerInterface
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
     * @param EntityManager   $em
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
                'Starting process transformData for Import#%d',
                $import->getId()
            ),
            ['group_id' => $import->getGroup()->getId()]
        );

        /** @var Value $accountingSystemRecord */
        foreach ($accountingSystemData as $accountingSystemRecord) {
            if (true === $this->checkExistImportPropertyInCache($import, $accountingSystemRecord)) {
                continue;
            }

            $importProperty = new ImportProperty();
            $importProperty->setImport($import);
            $importProperty->setExternalPropertyId($this->getExternalPropertyId($accountingSystemRecord));
            $importProperty->setExternalBuildingId($this->getExternalBuildingId($accountingSystemRecord));
            //PLS check
            $importProperty->setAddressHasUnits($this->getAddressHasUnits($accountingSystemRecord));
            //PLS check
            $importProperty->setPropertyHasBuildings($this->getPropertyHasBuildings($accountingSystemRecord));
            $importProperty->setUnitName($this->getUnitName($accountingSystemRecord));
            $importProperty->setExternalUnitId($this->getExternalUnitId($accountingSystemRecord));
            $importProperty->setAddress1($this->getAddress1($accountingSystemRecord));
            $importProperty->setCity($this->getCity($accountingSystemRecord));
            $importProperty->setState($this->getState($accountingSystemRecord));
            $importProperty->setZip($this->getZip($accountingSystemRecord));
            $importProperty->setAllowMultipleProperties($this->getAllowMultipleProperties($accountingSystemRecord));

            $this->em->persist($importProperty);

            $this->arrayCache[] = $import->getId() . $this->getExternalUnitId($accountingSystemRecord);
        }

        $this->em->flush();

        $this->logger->info(
            sprintf(
                'Finished process transformData for Import#%d',
                $import->getId()
            ),
            ['group_id' => $import->getGroup()->getId()]
        );
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalPropertyId(Value $accountingSystemRecord)
    {
        return $accountingSystemRecord->getPropertyId();
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalBuildingId(Value $accountingSystemRecord)
    {
        return $accountingSystemRecord->getBuildingId();
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return bool
     */
    protected function getAddressHasUnits(Value $accountingSystemRecord)
    {
        return true;
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return bool
     */
    protected function getPropertyHasBuildings(Value $accountingSystemRecord)
    {
        return false;
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(Value $accountingSystemRecord)
    {
        return $accountingSystemRecord->getUnitId();
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getExternalUnitId(Value $accountingSystemRecord)
    {
        return $accountingSystemRecord->getExternalUnitId();
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(Value $accountingSystemRecord)
    {
        $address = $accountingSystemRecord->getAddress() ?
            $accountingSystemRecord->getAddress() : $accountingSystemRecord->getBuildingAddress();

        return $address;
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getCity(Value $accountingSystemRecord)
    {
        return $accountingSystemRecord->getCity();
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getState(Value $accountingSystemRecord)
    {
        return $accountingSystemRecord->getState();
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getZip(Value $accountingSystemRecord)
    {
        return $accountingSystemRecord->getZipCode();
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return boolean
     */
    protected function getAllowMultipleProperties(Value $accountingSystemRecord)
    {
        return false;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\ImportPropertyRepository
     */
    protected function getImportPropertyRepository()
    {
        return $this->em->getRepository('RjDataBundle:ImportProperty');
    }

    /**
     * @param Import $import
     * @param Value  $accountingSystemRecord
     *
     * @return bool
     */
    protected function checkExistImportPropertyInCache(Import $import, Value $accountingSystemRecord)
    {
        return in_array($import->getId() . $this->getExternalUnitId($accountingSystemRecord), $this->arrayCache);
    }
}
