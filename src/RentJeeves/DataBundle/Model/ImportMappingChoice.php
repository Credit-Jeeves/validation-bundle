<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 */
abstract class ImportMappingChoice
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected $group;

    /**
    * @ORM\Column(
    *      name="header_hash",
    *      type="string",
    *      length=32,
    *      nullable=false
    * )
    */
    protected $headerHash;

    /**
    * @ORM\Column(
    *     name="mapping_data",
    *     type="array"
    * )
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
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getHeaderHash()
    {
        return $this->headerHash;
    }

    /**
     * @param string $headerHash
     */
    public function setHeaderHash($headerHash)
    {
        $this->headerHash = $headerHash;
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
