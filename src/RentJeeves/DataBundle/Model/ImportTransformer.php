<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class ImportTransformer
{
    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var Holding
     *
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Holding")
     * @ORM\JoinColumn(name="holding_id", referencedColumnName="id", nullable=true)
     */
    protected $holding;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=true)
     */
    protected $group;

    /**
     * @var string
     *
     * @ORM\Column(name="external_property_id", type="string", length=255, nullable=true)
     */
    protected $externalPropertyId;

    /**
     * @var string
     *
     * @ORM\Column(name="class_name", type="string", length=255, nullable=false)
     */
    protected $className;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }
}
