<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OrderRepository")
 * @ORM\Table(name="cj_order")
 * @ORM\HasLifecycleCallbacks()
 */
class Order
{
    /**
     * 
     * @var string
     */
    const STATUS_COMPLETE = 'complete';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $cj_applicant_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_date = new \DateTime();
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
     * Set cj_applicant_id
     *
     * @param integer $cjApplicantId
     * @return Order
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
     * Set status
     *
     * @param string $status
     * @return Order
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set created_date
     *
     * @param \DateTime $createdDate
     * @return Order
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_date
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_At;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Order
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