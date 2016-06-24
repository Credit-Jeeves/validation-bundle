<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ImportPropertyStatus;

/**
 * @ORM\MappedSuperclass
 */
abstract class ImportProperty
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="external_property_id",
     *     nullable=true
     * )
     * @var string
     */
    protected $externalPropertyId;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="external_building_id",
     *     nullable=true
     * )
     * @var string
     */
    protected $externalBuildingId;

    /**
     * @ORM\Column(
     *     name="address_has_units",
     *     type="boolean",
     *     options={
     *         "default"=1
     *     }
     * )
     * @var boolean
     */
    protected $addressHasUnits = true;

    /**
     * @ORM\Column(
     *     name="property_has_buildings",
     *     type="boolean",
     *     options={
     *         "default"=0
     *     }
     * )
     * @var boolean
     */
    protected $propertyHasBuildings = false;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="unit_name",
     *     nullable=true
     * )
     * @var string
     */
    protected $unitName;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="external_unit_id",
     *     nullable=true
     * )
     * @var string
     */
    protected $externalUnitId;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="address1",
     *     nullable=true
     * )
     * @var string
     */
    protected $address1;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     * @var string
     */
    protected $city;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     * @var string
     */
    protected $state;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=15,
     *     nullable=true
     * )
     * @var string
     */
    protected $zip;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     * @var string
     */
    protected $country;

    /**
     * @ORM\Column(
     *     name="account_number",
     *     type="string",
     *     nullable=true
     * )
     * @var string
     */
    protected $accountNumber;

    /**
     * @ORM\Column(
     *     type="ImportPropertyStatus",
     *     options={
     *         "default"="none"
     *     }
     * )
     * @var string
     */
    protected $status = ImportPropertyStatus::NONE;

    /**
     * @ORM\Column(
     *     name="error_messages",
     *     type="array",
     *     nullable=true
     * )
     * @var array
     */
    protected $errorMessages;

    /**
     * @ORM\Column(
     *     name="is_processed",
     *     type="boolean",
     *     options={
     *         "default"=0
     *     }
     * )
     * @var boolean
     */
    protected $processed = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="allow_multiple_properties", type="boolean", options={"default"=0})
     */
    protected $allowMultipleProperties = false;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\Import",
     *      inversedBy="importProperties",
     *      cascade={"persist", "merge"}
     * )
     * @ORM\JoinColumn(
     *      name="import_id",
     *      referencedColumnName="id",
     *      nullable=false
     * )
     *
     * @var \RentJeeves\DataBundle\Entity\Import
     */
    protected $import;

    /**
     * @return boolean
     */
    public function isAddressHasUnits()
    {
        return $this->addressHasUnits;
    }

    /**
     * @param boolean $addressHasUnits
     */
    public function setAddressHasUnits($addressHasUnits)
    {
        $this->addressHasUnits = $addressHasUnits;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getExternalBuildingId()
    {
        return $this->externalBuildingId;
    }

    /**
     * @param string $externalBuildingId
     */
    public function setExternalBuildingId($externalBuildingId)
    {
        $this->externalBuildingId = $externalBuildingId;
    }

    /**
     * @return string
     */
    public function getExternalPropertyId()
    {
        return $this->externalPropertyId;
    }

    /**
     * @param string $externalPropertyId
     */
    public function setExternalPropertyId($externalPropertyId)
    {
        $this->externalPropertyId = $externalPropertyId;
    }

    /**
     * @return string
     */
    public function getExternalUnitId()
    {
        return $this->externalUnitId;
    }

    /**
     * @param string $externalUnitId
     */
    public function setExternalUnitId($externalUnitId)
    {
        $this->externalUnitId = $externalUnitId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Import
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\Import $import
     */
    public function setImport($import)
    {
        $this->import = $import;
    }

    /**
     * @return boolean
     */
    public function isPropertyHasBuildings()
    {
        return $this->propertyHasBuildings;
    }

    /**
     * @param boolean $propertyHasBuildings
     */
    public function setPropertyHasBuildings($propertyHasBuildings)
    {
        $this->propertyHasBuildings = $propertyHasBuildings;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getUnitName()
    {
        return $this->unitName;
    }

    /**
     * @param string $unitName
     */
    public function setUnitName($unitName)
    {
        $this->unitName = $unitName;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @param array $errorMessages
     */
    public function setErrorMessages($errorMessages)
    {
        $this->errorMessages = $errorMessages;
    }

    /**
     * @return boolean
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * @param boolean $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return boolean
     */
    public function isAllowMultipleProperties()
    {
        return $this->allowMultipleProperties;
    }

    /**
     * @param boolean $allowMultipleProperties
     */
    public function setAllowMultipleProperties($allowMultipleProperties)
    {
        $this->allowMultipleProperties = $allowMultipleProperties;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }
}
