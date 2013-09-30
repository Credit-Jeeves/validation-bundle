<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

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
     *      targetEntity="RentJeeves\DataBundle\Entity\Tenant",
     *      inversedBy="payment_accounts",
     *      cascade={"all"}
     * )
     * @ORM\JoinColumn(
     *      name="user_id",
     *      referencedColumnName="id",
     *      nullable=false
     * )
     *
     * @Serializer\Exclude
     *
     * @var \RentJeeves\DataBundle\Entity\Tenant
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Group", inversedBy="paymentAccounts")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     * @Assert\Type(type="CreditJeeves\DataBundle\Entity\Group")
     *
     * @Serializer\Exclude
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Address",
     *      inversedBy="payment_accounts",
     *      cascade={"all"}
     * )
     * @ORM\JoinColumn(
     *      name="address_id",
     *      referencedColumnName="id"
     * )
     *
     * @Serializer\Exclude
     *
     * @var \CreditJeeves\DataBundle\Entity\Address
     */
    protected $address;

    /**
     * @ORM\Column(
     *      name="type",
     *      type="PaymentAccountType"
     * )
     * @Assert\NotBlank(
     *      message="checkout.error.payment_type.empty",
     *      groups={
     *          "card",
     *          "bank"
     *      }
     * )
     */
    protected $type;

    /**
     * @ORM\Column(
     *      name="name",
     *      type="string",
     *      length=255
     * )
     * @Assert\NotBlank(
     *      message="checkout.error.account_nickname.empty",
     *      groups={
     *          "save"
     *      }
     * )
     */
    protected $name;

    /**
     * @ORM\Column(
     *      name="token",
     *      type="string",
     *      length=255
     * )
     */
    protected $token;

    /**
     * @ORM\Column(
     *      name="cc_expiration",
     *      type="date",
     *      nullable=true
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

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Payment",
     *     mappedBy="paymentAccount",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     *
     * @Serializer\Exclude
     *
     * @var ArrayCollection
     */
    protected $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    /**
     * Set Tenant
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
     * Get Tenant
     *
     * @return \RentJeeves\DataBundle\Entity\Tenant
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set Group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @return PaymentAccount
     */
    public function setGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get Group
     *
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set address
     *
     * @param \CreditJeeves\DataBundle\Entity\Address $address
     * @return PaymentAccount
     */
    public function setAddress(\CreditJeeves\DataBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \CreditJeeves\DataBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
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
     * Set token
     *
     * @param string $token
     * @return PaymentAccount
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
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
     * Add Payment
     *
     * @param \RentJeeves\DataBundle\Entity\Payment $payment
     * @return PaymentAccount
     */
    public function addPayment(\RentJeeves\DataBundle\Entity\Payment $payment)
    {
        $this->payments[] = $payment;
        return $this;
    }

    /**
     * Remove Payment
     *
     * @param \RentJeeves\DataBundle\Entity\Payment $payment
     */
    public function removePayment(\RentJeeves\DataBundle\Entity\Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get Payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }
}
