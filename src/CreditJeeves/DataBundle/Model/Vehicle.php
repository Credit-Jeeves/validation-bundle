<?php 

namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Vehicle
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $cj_applicant_id;

    /**
     * @ORM\OneToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="vehicle")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $make;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $model;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $state;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $trade_in;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $down_payment;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

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
     * Set cj_applicant_id
     *
     * @param integer $cjApplicantId
     * @return Lead
     */
    public function setCjApplicantId($cjApplicantId)
    {
        $this->cj_applicant_id = $cjApplicantId;

        return $this;
    }

    /**
     * Get cj_applicant_id
     *
     * @return integer
     */
    public function getCjApplicantId()
    {
        return $this->cj_applicant_id;
    }

    /**
     * Set make
     *
     * @param string $make
     * @return Vehicle
     */
    public function setMake($make)
    {
        $this->make = $make;

        return $this;
    }

    /**
     * Get make
     *
     * @return string
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * Set model
     *
     * @param string $model
     * @return Vehicle
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Vehicle
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Vehicle
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set user
     *
     * @param \CreditJeeves\DataBundle\Entity\User $user
     * @return Vehicle
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
     * Set state
     *
     * @param integer $state
     * @return Vehicle
     */
    public function setState($state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return integer 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set trade_in
     *
     * @param boolean $tradeIn
     * @return Vehicle
     */
    public function setTradeIn($tradeIn)
    {
        $this->trade_in = $tradeIn;
    
        return $this;
    }

    /**
     * Get trade_in
     *
     * @return boolean 
     */
    public function getTradeIn()
    {
        return $this->trade_in;
    }

    /**
     * Set down_payment
     *
     * @param integer $downPayment
     * @return Vehicle
     */
    public function setDownPayment($downPayment)
    {
        $this->down_payment = $downPayment;
    
        return $this;
    }

    /**
     * Get down_payment
     *
     * @return integer 
     */
    public function getDownPayment()
    {
        return $this->down_payment;
    }
}