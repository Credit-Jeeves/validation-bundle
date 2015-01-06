<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @UniqueEntity(
 *     fields={"holding", "accountNumber"},
 *     groups={"unique_mapping"},
 *     errorPath="accountNumber",
 *     message="error.accountNumber.already_exists"
 * )
 */
abstract class GroupAccountNumberMapping
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="accountNumberMapping",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     * @Serializer\Exclude
     *
     * @var Group
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding"
     * )
     * @ORM\JoinColumn(
     *     name="holding_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     * @Serializer\Exclude
     *
     * @var Holding
     */
    protected $holding;

    /**
     * @ORM\Column(
     *      name="account_number",
     *      type="string",
     *      nullable=true,
     *      length=255
     * )
     * @Serializer\SerializedName("accountNumber")
     */
    protected $accountNumber;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param int $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
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
}
