<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AciCollectPaySettings
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Group
     *
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="aciCollectPaySettings"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     */
    protected $group;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="business_id",
     *     type="integer",
     *     nullable=false
     * )
     */
    protected $businessId;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="holder_name",
     *     type="string",
     *     nullable=false
     * )
     */
    protected $holderName;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param $businessId
     */
    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * @return int
     */
    public function getBusinessId()
    {
       return $this->businessId;
    }

    /**
     * @param $holderName
     */
    public function setHolderName($holderName)
    {
        $this->holderName = $holderName;
    }

    /**
     * @return string
     */
    public function getHolderName()
    {
        return $this->holderName;
    }
}
