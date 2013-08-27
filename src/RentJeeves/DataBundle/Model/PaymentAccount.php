<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass
 */
abstract class PaymentAccount
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Tenant",
     *     inversedBy="payment_accounts"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     * @var \RentJeeves\DataBundle\Entity\Tenant
     */
    protected $user;

    /**
     * @ORM\Column(
     *     name="name",
     *     type="string",
     *     length=255
     * )
     */
    protected $name;

    /**
     * @ORM\Column(
     *     name="account_name",
     *     type="string",
     *     length=255
     * )
     */
    protected $accountName;

    /**
     * @ORM\Column(
     *     name="number",
     *     type="string",
     *     length=255
     * )
     */
    protected $number;

    /**
     * @ORM\Column(
     *     name="routing",
     *     type="string",
     *     length=255
     * )
     */
    protected $routing;

    /**
     * @ORM\Column(
     *     name="type",
     *     type="PaymentAccountType"
     * )
     */
    protected $type;

    /**
     * @ORM\Column(
     *     name="cc_expiration",
     *     type="date"
     * )
     */
    protected $ccExpiration;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     */
    protected $updatedAt;

    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return PaymentAccount
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set accountName
     *
     * @param string $accountName
     * @return PaymentAccount
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * Get accountName
     *
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Set number
     *
     * @param string $number
     * @return PaymentAccount
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set routing
     *
     * @param string $routing
     * @return PaymentAccount
     */
    public function setRouting($routing)
    {
        $this->routing = $routing;

        return $this;
    }

    /**
     * Get routing
     *
     * @return string
     */
    public function getRouting()
    {
        return $this->routing;
    }

    /**
     * Set type
     *
     * @param PaymentAccountType $type
     * @return PaymentAccount
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return PaymentAccountType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set ccExpiration
     *
     * @param \DateTime $ccExpiration
     * @return PaymentAccount
     */
    public function setCcExpiration($ccExpiration)
    {
        $this->ccExpiration = $ccExpiration;

        return $this;
    }

    /**
     * Get ccExpiration
     *
     * @return \DateTime
     */
    public function getCcExpiration()
    {
        return $this->ccExpiration;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PaymentAccount
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return PaymentAccount
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set user
     *
     * @param \RentJeeves\DataBundle\Entity\Tenant $user
     * @return PaymentAccount
     */
    public function setUser(\RentJeeves\DataBundle\Entity\Tenant $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \RentJeeves\DataBundle\Entity\Tenant
     */
    public function getUser()
    {
        return $this->user;
    }
}
