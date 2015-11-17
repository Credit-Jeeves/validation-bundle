<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\CoreBundle\Type\Encrypt;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @Serializer\ExclusionPolicy("all")
 */
abstract class MailingAddress
{
    /**
     * @var integer
     *
     * @ORM\Column(
     *     name="id",
     *     type="bigint"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(
     *     name="user_id",
     *     type="bigint"
     * )
     */
    protected $userId;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     inversedBy="addresses"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="unit",
     *     type="encrypt",
     *     nullable=true
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $unit;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="number",
     *     type="encrypt",
     *     nullable=true
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $number;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="encrypt")
     * @Assert\NotBlank(
     *     message="error.user.street.empty",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "account_landlord",
     *         "api_identity_check"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "account_landlord",
     *         "api_identity_check"
     *     }
     * )
     * @Assert\Regex(
     *     pattern="/^([-a-zA-Z0-9\/\. #]{2,68})$/",
     *     message="error.user.street.invalid",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "account_landlord",
     *         "api_identity_check"
     *     }
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=15, nullable=true)
     * @Assert\NotBlank(
     *     message="error.user.zip.empty",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "api_identity_check"
     *     }
     * )
     * @Assert\Length(
     *     min=4,
     *     max=6,
     *     maxMessage = "Zip code cannot be longer than {{ limit }} characters length",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "api_identity_check"
     *     }
     * )
     * @Assert\Regex(
     *     pattern="/^(\d)+$/",
     *     message="error.user.zip.invalid",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "api_identity_check"
     *     }
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $zip;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="district",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $district;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="city",
     *     type="string",
     *     length=255
     * )
     * @Assert\NotBlank(
     *     message="error.user.city.empty",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "account_landlord",
     *         "api_identity_check"
     *     }
     * )
     * @Assert\Regex(
     *     pattern="/(^[-a-zA-Z ]{2,38})$/",
     *     message="error.user.city.invalid",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "account_landlord",
     *         "api_identity_check"
     *     }
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="area", type="string", nullable=true)
     * @Assert\NotBlank(
     *     message="error.user.state.empty",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "api_identity_check"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "user_address_new",
     *         "buy_report_new",
     *         "api_identity_check"
     *     }
     * )
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $area;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=3, options={"default"="US"})
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $country = 'US';

    /**
     * @var string
     *
     * @ORM\Column(name="is_default", type="boolean", options={"default"="0"})
     * @Serializer\Expose
     * @Serializer\Groups({"paymentAccounts"});
     */
    protected $isDefault = false;
    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\PaymentAccount",
     *     mappedBy="address",
     *     cascade={
     *         "persist",
     *         "merge"
     *     }
     * )
     *
     */
    protected $paymentAccounts;

    public function __construct()
    {
        $this->paymentAccounts = new ArrayCollection();
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
     * Set userId
     *
     * @param integer $userId
     * @return Address
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set unit
     *
     * @param string $unit
     * @return Address
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit
     *
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set number
     *
     * @param encrypt $number
     * @return Address
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return encrypt
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set street
     *
     * @param encrypt $street
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return encrypt
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return Address
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set district
     *
     * @param string $district
     * @return Address
     */
    public function setDistrict($district)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return Address
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set is_default
     *
     * @param bool $isDefault
     * @return Address
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get is_default
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Address
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
     * @return Address
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
     * @param \CreditJeeves\DataBundle\Entity\User $user
     * @return Address
     */
    public function setUser(\CreditJeeves\DataBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return ArrayCollection|PaymentAccount[]
     */
    public function getPaymentAccounts()
    {
        return $this->paymentAccounts;
    }
}
