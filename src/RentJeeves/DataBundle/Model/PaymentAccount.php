<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Entity\Payment as PaymentEntity;
use RentJeeves\DataBundle\Entity\PaymentAccountHpsMerchant as PaymentAccountHpsMerchantEntity;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 * @Serializer\XmlRoot("request")
 */
abstract class PaymentAccount
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"basic", "paymentAccounts"});
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\Tenant",
     *      inversedBy="paymentAccounts",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *      name="user_id",
     *      referencedColumnName="id",
     *      nullable=false
     * )
     *
     * @var \RentJeeves\DataBundle\Entity\Tenant
     */
    protected $user;

    /**
     * @ORM\Column(
     *     type="PaymentProcessor",
     *     options={
     *         "default"="heartland"
     *     },
     *     name="payment_processor",
     *     nullable=false
     * )
     */
    protected $paymentProcessor = PaymentProcessor::HEARTLAND;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="CreditJeeves\DataBundle\Entity\MailingAddress",
     *      inversedBy="paymentAccounts",
     *      cascade={"persist"},
     *      fetch="EAGER"
     * )
     * @ORM\JoinColumn(
     *      name="address_id",
     *      referencedColumnName="id",
     *      onDelete="SET NULL"
     * )
     *
     * @Serializer\SerializedName("addressId")
     * @Serializer\Accessor(getter="getAddressId")
     * @Serializer\Groups({"basic", "paymentAccounts"});
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
     *          "bank",
     *          "debit_card"
     *      }
     * )
     * @Serializer\Groups({"basic", "paymentAccounts"});
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
     *          "card",
     *          "bank",
     *          "debit_card"
     *      }
     * )
     * @Serializer\Groups({"basic", "paymentAccounts"});
     */
    protected $name;

    /**
     * @ORM\Column(
     *     name="token",
     *     type="string",
     *     length=255
     * )
     */
    protected $token;

    /**
     * @ORM\Column(
     *     name="cc_expiration",
     *     type="date",
     *     nullable=true
     * )
     * @Serializer\Type("DateTime<'Y-m-d'>");
     * @Serializer\Groups({"basic", "paymentAccounts"});
     */
    protected $ccExpiration;

    /**
     * @ORM\Column(
     *     name="last_four",
     *     type="string",
     *     length=4,
     *     nullable=true
     * )
     * @Serializer\Type("string");
     * @Serializer\Groups({"basic", "paymentAccounts"});
     */
    protected $lastFour;

    /**
     * @ORM\Column(
     *      name="bank_account_type",
     *      type="BankAccountType",
     *      nullable=true
     * )
     */
    protected $bankAccountType;

    /**
     * @ORM\Column(
     *      name="debit_type",
     *      type="DebitType",
     *      nullable=true
     * )
     */
    protected $debitType;

    /**
     * @var boolean
     *
     * @ORM\Column(
     *      type="boolean",
     *      options={
     *          "default" : 0
     *      },
     *     nullable=false
     * )
     */
    protected $registered = false;

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
     * @ORM\Column(
     *      name="deleted_at",
     *      type="datetime",
     *      nullable=true
     * )
     */
    protected $deletedAt;

    /**
     * @ORM\OneToMany(
     *      targetEntity="RentJeeves\DataBundle\Entity\Payment",
     *      mappedBy="paymentAccount",
     *      cascade={"persist", "remove", "merge"},
     *      orphanRemoval=true
     * )
     * @var ArrayCollection
     */
    protected $payments;

    /**
     * @ORM\OneToMany(
     *      targetEntity="RentJeeves\DataBundle\Entity\PaymentAccountHpsMerchant",
     *      mappedBy="paymentAccount",
     *      cascade={"persist", "remove", "merge"}
     * )
     * @var ArrayCollection
     */
    protected $hpsMerchants;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\UserSettings",
     *     mappedBy="creditTrackPaymentAccount",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=false
     * )
     * @var UserSettings
     */
    protected $creditTrackUserSetting;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\JobRelatedCreditTrack",
     *     mappedBy="creditTrackPaymentAccount",
     *     cascade={"persist", "merge"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    protected $creditTrackJobs;

    /**
     * @ORM\OneToMany(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Order",
     *      mappedBy="paymentAccount",
     *      cascade={"persist"}
     * )
     * @var ArrayCollection
     */
    protected $orders;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->creditTrackJobs = new ArrayCollection();
        $this->hpsMerchants = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(User $user = null)
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
     * Set address
     *
     * @param  \CreditJeeves\DataBundle\Entity\MailingAddress $address
     * @return PaymentAccount
     */
    public function setAddress(\CreditJeeves\DataBundle\Entity\MailingAddress $address = null)
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
     * Get address Id
     *
     * @return int
     */
    public function getAddressId()
    {
        return empty($this->address) ? null : $this->address->getId();
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
     * @param  PaymentAccountType $type
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
     * @param  string         $name
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
     * @param  string         $token
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
     * @param  \DateTime      $ccExpiration
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
     * Set ACH Type for bank account only
     *
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
     * Set createdAt
     *
     * @param  \DateTime      $createdAt
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
     * @param  \DateTime      $updatedAt
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
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add Payment
     *
     * @param  \RentJeeves\DataBundle\Entity\Payment $payment
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
     * @return \Doctrine\Common\Collections\Collection|PaymentEntity[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Get UserSettings
     *
     * @return UserSettings
     */
    public function getCreditTrackUserSetting()
    {
        return $this->creditTrackUserSetting;
    }

    /**
     * @param string $paymentProcessor
     */
    public function setPaymentProcessor($paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    /**
     * @return string
     */
    public function getPaymentProcessor()
    {
        return $this->paymentProcessor;
    }

    /**
     * @return ArrayCollection|Order[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param Order $order
     */
    public function addOrder(Order $order)
    {
        $this->orders->add($order);
    }

    /**
     * @return ArrayCollection|PaymentAccountHpsMerchantEntity
     */
    public function getHpsMerchants()
    {
        return $this->hpsMerchants;
    }

    /**
     * @param PaymentAccountHpsMerchantEntity $hpsMerchant
     */
    public function addHpsMerchant(PaymentAccountHpsMerchantEntity $hpsMerchant)
    {
        $this->hpsMerchants[] = $hpsMerchant;
    }

    /**
     * @param PaymentAccountHpsMerchantEntity $hpsMerchant
     */
    public function removeHpsMerchant(PaymentAccountHpsMerchantEntity $hpsMerchant)
    {
        $this->hpsMerchants->removeElement($hpsMerchant);
    }

    /**
     * @return boolean
     */
    public function isRegistered()
    {
        return $this->registered;
    }

    /**
     * @param boolean $registered
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;
    }

    /**
     * @return string
     */
    public function getLastFour()
    {
        return $this->lastFour;
    }

    /**
     * @param string $lastFour
     */
    public function setLastFour($lastFour)
    {
        $this->lastFour = $lastFour;
    }

    /**
     * @return string
     */
    public function getDebitType()
    {
        return $this->debitType;
    }

    /**
     * @param string $debitType
     */
    public function setDebitType($debitType)
    {
        $this->debitType = $debitType;
    }
}
