<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

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
     *     nullable=false
     * )
     * @var string
     */
    protected $externalPropertyId;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="external_building_id",
     *     nullable=false
     * )
     * @var string
     */
    protected $externalBuildingId;

    /**
     * @ORM\Column(
     *     name="address_has_units",
     *     type="boolean",
     *     nullable=false
     * )
     * @var boolean
     */
    protected $addressHasUnits;

    /**
     * @ORM\Column(
     *     name="property_has_buildings",
     *     type="boolean",
     *     nullable=false
     * )
     * @var boolean
     */
    protected $propertyHasBuildings;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="unit_name",
     *     nullable=false
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
     *     name="street_number",
     *     nullable=false
     * )
     * @var string
     */
    protected $streetNumber;

    /**
     * @ORM\Column(
     *     type="string",
     *     name="street_name",
     *     nullable=false
     * )
     * @var string
     */
    protected $streetName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $city;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $state;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=15
     * )
     * @var string
     */
    protected $zip;

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
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * @param string $streetName
     */
    public function setStreetName($streetName)
    {
        $this->streetName = $streetName;
    }

    /**
     * @return string
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * @param string $streetNumber
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;
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
}
