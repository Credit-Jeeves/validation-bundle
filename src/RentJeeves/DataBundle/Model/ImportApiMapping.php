<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class ImportApiMapping
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="importApiMapping"
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id"
     * )
     *
     * @var Holding
     */
    protected $holding;

    /**
     * @ORM\Column(
     *     name="external_property_id",
     *     type="string"
     * )
     *
     * @var string
     */
    protected $externalPropertyId;

    /**
     * @ORM\Column(
     *     name="street",
     *     type="string"
     * )
     *
     * @var string
     */
    protected $street;

    /**
     * @ORM\Column(
     *     name="city",
     *     type="string"
     * )
     *
     * @var string
     */
    protected $city;

    /**
     * @ORM\Column(
     *     name="state",
     *     type="string"
     * )
     *
     * @var string
     */
    protected $state;

    /**
     * @ORM\Column(
     *     name="zip",
     *     type="string",
     *     length=15
     * )
     *
     * @var string
     */
    protected $zip;

    /**
    * @ORM\Column(
    *     name="mapping_data",
    *     type="array"
    * )
     *
    * @var array
    */
    protected $mappingData;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getMappingData()
    {
        return $this->mappingData;
    }

    /**
     * @param array $mappingData
     */
    public function setMappingData($mappingData)
    {
        $this->mappingData = $mappingData;
    }

    /**
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * @param Holding $holding
     */
    public function setHolding(Holding $holding)
    {
        $this->holding = $holding;
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
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
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
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }
}
