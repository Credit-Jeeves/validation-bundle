<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pricing
 *
 * @ORM\Table(name="cj_pricing")
 * @ORM\Entity
 *
 * @deprecated Not in use?
 */
class Pricing
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="cj_account_group_id", type="integer")
     */
    private $cjAccountGroupId;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="bigint")
     */
    private $amount;


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
     * Set cjAccountGroupId
     *
     * @param integer $cjAccountGroupId
     * @return Pricing
     */
    public function setCjAccountGroupId($cjAccountGroupId)
    {
        $this->cjAccountGroupId = $cjAccountGroupId;
    
        return $this;
    }

    /**
     * Get cjAccountGroupId
     *
     * @return integer 
     */
    public function getCjAccountGroupId()
    {
        return $this->cjAccountGroupId;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return Pricing
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
}
