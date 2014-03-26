<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\LeadStatus;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\MappedSuperclass
 */
abstract class LeadHistory extends AbstractLogEntry
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
     * @ORM\Column(name="editor_id", type="bigint", nullable=true)
     */
    protected $editorId;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="bigint", nullable=true)
     */
    protected $objectId;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Lead", inversedBy="histories")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     */
    protected $object;

    /**
     * @var integer
     *
     * @ORM\Column(name="target_score", type="bigint", nullable=true)
     */
    protected $targetScore;

    /**
     * @var string
     *
     * @ORM\Column(name="target_name", type="text", nullable=true)
     */
    protected $targetName;

    /**
     * @var string
     *
     * @ORM\Column(name="target_url", type="text", nullable=true)
     */
    protected $targetUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="state", type="bigint", nullable=true)
     */
    protected $state;

    /**
     * @var integer
     *
     * @ORM\Column(name="trade_in", type="boolean", nullable=true)
     */
    protected $tradeIn;

    /**
     * @var integer
     *
     * @ORM\Column(name="down_payment", type="bigint", nullable=true)
     */
    protected $downPayment;

    /**
     * @var integer
     *
     * @ORM\Column(name="fraction", type="smallint", nullable=true, options={"default"="0"})
     */
    protected $fraction = 0;

    /**
     * @ORM\Column(name="status", type="LeadStatus", options={"default"="new"})
     */
    protected $status = LeadStatus::NEWONE;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @var string $action
     *
     * @~ORM\Column(type="string", length=8)
     */
    protected $action;

    /**
     * @var string $loggedAt
     *
     * @~ORM\Column(name="logged_at", type="datetime")
     */
    protected $loggedAt;

    /**
     * @var string $objectClass
     *
     * @~ORM\Column(name="object_class", type="string", length=255)
     */
    protected $objectClass;

    /**
     * @var integer $version
     *
     * @~ORM\Column(type="bigint")
     */
    protected $version;

    /**
     * @var string $data
     *
     * @~ORM\Column(type="array", nullable=true)
     */
    protected $data;

    /**
     * @var string $data
     *
     * @~ORM\Column(length=255, nullable=true)
     */
    protected $username;


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
     * Set editorId
     *
     * @param integer $editorId
     * @return LeadHistory
     */
    public function setEditorId($editorId)
    {
        $this->editorId = $editorId;
    
        return $this;
    }

    /**
     * Get editorId
     *
     * @return integer 
     */
    public function getEditorId()
    {
        return $this->editorId;
    }

    /**
     * @param $object
     *
     * @return $this
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Lead
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set targetScore
     *
     * @param integer $targetScore
     * @return LeadHistory
     */
    public function setTargetScore($targetScore)
    {
        $this->targetScore = $targetScore;
    
        return $this;
    }

    /**
     * Get targetScore
     *
     * @return integer 
     */
    public function getTargetScore()
    {
        return $this->targetScore;
    }

    /**
     * Set targetName
     *
     * @param string $targetName
     * @return LeadHistory
     */
    public function setTargetName($targetName)
    {
        $this->targetName = $targetName;
    
        return $this;
    }

    /**
     * Get targetName
     *
     * @return string 
     */
    public function getTargetName()
    {
        return $this->targetName;
    }

    /**
     * Set targetUrl
     *
     * @param string $targetUrl
     * @return LeadHistory
     */
    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;
    
        return $this;
    }

    /**
     * Get targetUrl
     *
     * @return string 
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return LeadHistory
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
     * Set tradeIn
     *
     * @param integer $tradeIn
     * @return LeadHistory
     */
    public function setTradeIn($tradeIn)
    {
        $this->tradeIn = $tradeIn;
    
        return $this;
    }

    /**
     * Get tradeIn
     *
     * @return integer 
     */
    public function getTradeIn()
    {
        return $this->tradeIn;
    }

    /**
     * Set downPayment
     *
     * @param integer $downPayment
     * @return LeadHistory
     */
    public function setDownPayment($downPayment)
    {
        $this->downPayment = $downPayment;
    
        return $this;
    }

    /**
     * Get downPayment
     *
     * @return integer 
     */
    public function getDownPayment()
    {
        return $this->downPayment;
    }

    /**
     * Set fraction
     *
     * @param integer $fraction
     * @return LeadHistory
     */
    public function setFraction($fraction)
    {
        $this->fraction = $fraction;
    
        return $this;
    }

    /**
     * Get fraction
     *
     * @return integer 
     */
    public function getFraction()
    {
        return $this->fraction;
    }

    /**
     * Set status
     *
     * @param LeadStatus $status
     * @return LeadHistory
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return LeadStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set updatedAt
     *
     * @param DateTime $updatedAt
     * @return LeadHistory
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
