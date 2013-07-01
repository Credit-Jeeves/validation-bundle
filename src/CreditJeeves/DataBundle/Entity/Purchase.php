<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Purchase
 *
 * @ORM\Table(name="cj_purchase")
 * @ORM\Entity
 *
 * @deprecated Not in use?
 */
class Purchase
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
     * @ORM\Column(name="amount", type="bigint")
     */
    protected $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_lead_id", type="bigint")
     */
    protected $cjLeadId;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_account_id", type="bigint")
     */
    protected $cjAccountId;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;


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
     * Set amount
     *
     * @param integer $amount
     * @return Purchase
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set cjLeadId
     *
     * @param integer $cjLeadId
     * @return Purchase
     */
    public function setCjLeadId($cjLeadId)
    {
        $this->cjLeadId = $cjLeadId;
    
        return $this;
    }

    /**
     * Get cjLeadId
     *
     * @return integer 
     */
    public function getCjLeadId()
    {
        return $this->cjLeadId;
    }

    /**
     * Set cjAccountId
     *
     * @param integer $cjAccountId
     * @return Purchase
     */
    public function setCjAccountId($cjAccountId)
    {
        $this->cjAccountId = $cjAccountId;
    
        return $this;
    }

    /**
     * Get cjAccountId
     *
     * @return integer 
     */
    public function getCjAccountId()
    {
        return $this->cjAccountId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Purchase
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
}
