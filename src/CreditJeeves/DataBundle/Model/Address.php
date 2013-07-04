<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Address
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="bigint")
     */
    protected $userId;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="addresses")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="encrypt")
     * @Assert\NotBlank(
     *     message="error.user.address.empty",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "user_address_new",
     *         "buy_report_new"
     *     }
     * )
     */
    protected $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="encrypt")
     * @Assert\NotBlank(
     *     message="error.user.unit.empty",
     *     groups={
     *         "user_address_new"
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=31,
     *     groups={
     *         "user_address_new"
     *     }
     * )
     */
    protected $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     * @Assert\NotBlank(
     *     message="error.user.city.empty",
     *     groups={
     *         "buy_report_new"
     *     }
     * )
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=7, nullable=true)
     * @Assert\NotBlank(
     *     message="error.user.state.empty",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new"
     *     }
     * )
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     groups={
     *         "user_address_new",
     *         "buy_report_new"
     *     }
     * )
     */
    protected $state;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=15)
     * @Assert\NotBlank(
     *     message="error.user.zip.empty",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new"
     *     }
     * )
     * @Assert\Length(
     *     min=1,
     *     max=15,
     *     maxMessage = "Zip code cannot be longer than {{ limit }} characters length",
     *     groups={
     *         "user_address_new",
     *         "buy_report_new"
     *     }
     * )
     */
    protected $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=3, nullable=true, options={"default"="USA"})
     */
    protected $country = 'USA';

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
     * Set address1
     *
     * @param string $address1
     * @return Address
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    
        return $this;
    }

    /**
     * Get address1
     *
     * @return string 
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     * @return Address
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    
        return $this;
    }

    /**
     * Get address2
     *
     * @return string 
     */
    public function getAddress2()
    {
        return $this->address2;
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
     * Set state
     *
     * @param string $state
     * @return Address
     */
    public function setState($state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
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
     * @return Order
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
}
