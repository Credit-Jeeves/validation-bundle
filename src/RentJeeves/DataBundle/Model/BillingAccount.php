<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;
use RentJeeves\DataBundle\Enum\BankAccountType;

/**
 * @ORM\MappedSuperclass
 */
abstract class BillingAccount
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Type("integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="billingAccounts",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     * @Serializer\Exclude
     */
    protected $group;

    /**
     * @ORM\Column(
     *      name="token",
     *      type="string",
     *      length=255
     * )
     * @Serializer\Exclude
     */
    protected $token;

    /**
     * @ORM\Column(
     *      name="nickname",
     *      type="string",
     *      length=255
     * )
     * @Type("string")
     */
    protected $nickname;

    /**
     * @ORM\Column(
     *      name="bank_account_type",
     *      type="BankAccountType",
     *      nullable=false
     * )
     */
    protected $bankAccountType;

    /**
     * @ORM\Column(
     *     name="active",
     *     type="boolean",
     *     options={
     *         "default"="0"
     *     }
     * )
     * @Type("boolean")
     * @Serializer\SerializedName("isActive")
     */
    protected $isActive = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $nickname
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param string $bankAccountType
     * @see BankAccountType
     * @return PaymentAccount
     */
    public function setBankAccountType($bankAccountType)
    {
        $this->bankAccountType = $bankAccountType;
    }

    /**
     * Get ACH Type for bank account only
     *
     * @return string
     * @see BankAccountType
     */
    public function getBankAccountType()
    {
        return $this->bankAccountType;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
