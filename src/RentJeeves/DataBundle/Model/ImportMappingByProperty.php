<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class ImportMappingByProperty
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="importMappingByProperty"
     * )
     * @ORM\JoinColumn(
     *      name="property_id",
     *      referencedColumnName="id",
     *      nullable=false,
     *      unique=true
     * )
     *
     * @var Property
     */
    protected $property;

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
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;
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
}
