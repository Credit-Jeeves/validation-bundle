<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks() 
 */
abstract class Invite
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
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="error.user.first_name.short",
     *     maxMessage="error.user.first_name.long",
     *     groups={
     *         "invite",
     *     }
     * )     
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="error.user.first_name.short",
     *     maxMessage="error.user.first_name.long",
     *     groups={
     *         "invite",
     *     }
     * )     
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=50, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100)
     * @Assert\NotBlank(
     *     message="error.user.last_name.empty",
     *     groups={
     *         "invite",
     *     }
     * )
     * @Assert\Email(
     *     groups={
     *         "invite",
     *     }
     * )     
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=50, nullable=true)
     */
    protected $unit;


    /**
     * @ORM\OneToOne(targetEntity="CreditJeeves\DataBundle\Entity\Tenant", inversedBy="invite", cascade={"persist", "merge"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\NotBlank(
     *     message="error.user.tenant.empty",
     *     groups={
     *         "invite",
     *     }
     * )     
     */
    protected $tenant;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Property",
     *     inversedBy="invite"
     * )
     * @ORM\JoinColumn(
     *     name="property_id",
     *     referencedColumnName="id"
     * )
     */
    protected $property;

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
     * Set firstName
     *
     * @param string $firstName
     * @return Invite
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Invite
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Invite
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Invite
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set unit
     *
     * @param string $unit
     * @return Invite
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
     * Set tenant
     *
     * @param \CreditJeeves\DataBundle\Entity\Tenant $tenant
     * @return Invite
     */
    public function setTenant(\CreditJeeves\DataBundle\Entity\Tenant $tenant = null)
    {
        $this->tenant = $tenant;
    
        return $this;
    }

    /**
     * Get tenant
     *
     * @return \CreditJeeves\DataBundle\Entity\Tenant 
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Set property
     *
     * @param \RentJeeves\DataBundle\Entity\Property $property
     * @return Invite
     */
    public function setProperty(\RentJeeves\DataBundle\Entity\Property $property = null)
    {
        $this->property = $property;
    
        return $this;
    }

    /**
     * Get property
     *
     * @return \RentJeeves\DataBundle\Entity\Property 
     */
    public function getProperty()
    {
        return $this->property;
    }
}
