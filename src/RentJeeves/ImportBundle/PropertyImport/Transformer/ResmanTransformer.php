<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtUnit;

/**
 * Service`s name "import.property.transformer.resman"
 */
class ResmanTransformer implements TransformerInterface
{
    /**
     * @var array
     */
    protected $arrayCache = [];

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
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
            ['group_id' => $import->getGroup()->getId()]
        );

        /** @var RtCustomer $accountingSystemRecord */
        foreach (current($accountingSystemData) as $accountingSystemRecord) {
            $rtUnit = $accountingSystemRecord->getRtUnit();
            if ($accountingSystemRecord->getCustomers()->getCustomer()->count() === 0) {
                continue;
            }
            /** @var Customer $customer */
            foreach ($accountingSystemRecord->getCustomers()->getCustomer() as $customer) {
                $extUnitId = $this->getExternalUnitId($customer, $accountingSystemRecord);
                if (true === $this->checkExistImportPropertyInCache($import, $extUnitId)) {
                    continue;
                }
                $importProperty = new ImportProperty();
                $importProperty->setImport($import);
                $importProperty->setExternalPropertyId($this->getExternalPropertyId($customer));
                $importProperty->setExternalBuildingId($this->getExternalBuildingId($rtUnit));
                //PLS check
                $importProperty->setAddressHasUnits($this->getAddressHasUnits($rtUnit));
                //PLS check
                $importProperty->setPropertyHasBuildings($this->getPropertyHasBuildings($rtUnit));
                $importProperty->setUnitName($this->getUnitName($rtUnit));
                $importProperty->setExternalUnitId($extUnitId);
                $importProperty->setAddress1($this->getAddress1($customer));
                $importProperty->setCity($this->getCity($customer));
                $importProperty->setState($this->getState($customer));
                $importProperty->setZip($this->getZip($customer));
                $importProperty->setAllowMultipleProperties($this->getAllowMultipleProperties($customer));

                $this->em->persist($importProperty);
                $this->arrayCache[] = $import->getId() . $extUnitId;
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
    }

    /**
     * @param Customer $customer
     *
     * @return string
     */
    protected function getExternalPropertyId(Customer $customer)
    {
        return $customer->getProperty()->getPrimaryId();
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
     * @param Customer   $customer
     * @param RtCustomer $rtCustomer
     *
     * @return string
     */
    protected function getExternalUnitId(Customer $customer, RtCustomer $rtCustomer)
    {
        return $customer->getExternalUnitId($rtCustomer);
    }

    /**
     * @param Customer $customer
     *
     * @return string
     */
    protected function getAddress1(Customer $customer)
    {
        return $customer->getAddress()->getAddress1();
    }

    /**
     * @param Customer $customer
     *
     * @return string
     */
    protected function getCity(Customer $customer)
    {
        return $customer->getAddress()->getCity();
    }

    /**
     * @param Customer $customer
     *
     * @return string
     */
    protected function getState(Customer $customer)
    {
        return $customer->getAddress()->getState();
    }

    /**
     * @param Customer $customer
     *
     * @return string
     */
    protected function getZip(Customer $customer)
    {
        return $customer->getAddress()->getPostalCode();
    }

    /**
     * @param Customer $customer
     *
     * @return boolean
     */
    protected function getAllowMultipleProperties(Customer $customer)
    {
        return false;
    }

    /**
     * @param Import $import
     * @param string  $extUnitId
     *
     * @return bool
     */
    protected function checkExistImportPropertyInCache(Import $import, $extUnitId)
    {
        return in_array($import->getId() . $extUnitId, $this->arrayCache);
    }
}
