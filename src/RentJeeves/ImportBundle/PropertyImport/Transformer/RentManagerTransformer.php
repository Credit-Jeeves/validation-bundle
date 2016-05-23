<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ImportBundle\Exception\ImportTransformerException;
use RentTrack\RentManagerClientBundle\Model\Address;
use RentTrack\RentManagerClientBundle\Model\Property;
use RentTrack\RentManagerClientBundle\Model\Unit;

/**
 * Service`s name "import.property.transformer.rent_manager"
 */
class RentManagerTransformer implements TransformerInterface
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
                'Starting process transformData for Import#%d',
                $import->getId()
            ),
            ['group' => $import->getGroup()]
        );

        $rentManagerProperty = $accountingSystemData['property'];
        $rentManagerUnits = $accountingSystemData['units'];
        foreach ($rentManagerUnits as $rentManagerUnit) {
            if (true === $this->checkExistImportPropertyInCache($import, $rentManagerProperty, $rentManagerUnit)) {
                continue;
            }
            $importProperty = new ImportProperty();
            $importProperty->setImport($import);
            $importProperty->setExternalPropertyId(
                $this->getExternalPropertyId($rentManagerProperty, $rentManagerUnit)
            );
            $importProperty->setExternalBuildingId(
                $this->getExternalBuildingId($rentManagerProperty, $rentManagerUnit)
            );
            $importProperty->setAddressHasUnits(
                $this->getAddressHasUnits($rentManagerProperty, $rentManagerUnit)
            );
            $importProperty->setPropertyHasBuildings(
                $this->getPropertyHasBuildings($rentManagerProperty, $rentManagerUnit)
            );
            $importProperty->setUnitName($this->getUnitName($rentManagerProperty, $rentManagerUnit));
            $importProperty->setExternalUnitId($this->getExternalUnitId($rentManagerProperty, $rentManagerUnit));
            $importProperty->setAddress1($this->getAddress1($rentManagerProperty, $rentManagerUnit));
            $importProperty->setCity($this->getCity($rentManagerProperty, $rentManagerUnit));
            $importProperty->setState($this->getState($rentManagerProperty, $rentManagerUnit));
            $importProperty->setZip($this->getZip($rentManagerProperty, $rentManagerUnit));
            $importProperty->setAllowMultipleProperties(
                $this->getAllowMultipleProperties($rentManagerProperty, $rentManagerUnit)
            );

            $this->em->persist($importProperty);

            $this->arrayCache[] = $import->getId() . '|'
                . $this->getExternalUnitId($rentManagerProperty, $rentManagerUnit);
        }

        $this->em->flush();

        $this->logger->info(
            sprintf(
                'Finished process transformData for Import#%d',
                $import->getId()
            ),
            ['group' => $import->getGroup()]
        );
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getExternalPropertyId(Property $property, Unit $unit)
    {
        return $property->getShortName();
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getExternalBuildingId(Property $property, Unit $unit)
    {
        return null;
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getAddressHasUnits(Property $property, Unit $unit)
    {
        return true;
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getPropertyHasBuildings(Property $property, Unit $unit)
    {
        return false;
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getUnitName(Property $property, Unit $unit)
    {
        return $unit->getName();
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getExternalUnitId(Property $property, Unit $unit)
    {
        return $unit->getUnitId();
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getAddress1(Property $property, Unit $unit)
    {
        return $this->getPrimaryAddressByUnit($unit)->getStreet1();
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getCity(Property $property, Unit $unit)
    {
        return $this->getPrimaryAddressByUnit($unit)->getCity();
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getState(Property $property, Unit $unit)
    {
        return $this->getPrimaryAddressByUnit($unit)->getState();
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getZip(Property $property, Unit $unit)
    {
        return $this->getPrimaryAddressByUnit($unit)->getPostalCode();
    }

    /**
     * @param Property $property
     * @param Unit     $unit
     *
     * @return string
     */
    protected function getAllowMultipleProperties(Property $property, Unit $unit)
    {
        return false;
    }

    /**
     * @param Import   $import
     * @param Property $property
     * @param Unit     $unit
     *
     * @return bool
     */
    protected function checkExistImportPropertyInCache(Import $import, Property $property, Unit $unit)
    {
        return in_array($import->getId() . '|' . $this->getExternalUnitId($property, $unit), $this->arrayCache);
    }

    /**
     * @param Unit $unit
     *
     * @return Address
     *
     * @throws ImportTransformerException
     */
    protected function getPrimaryAddressByUnit(Unit $unit)
    {
        foreach ($unit->getAddresses() as $address) {
            if ($address->getIsPrimary() == true) {
                return $address;
            }
        }
    }
}
