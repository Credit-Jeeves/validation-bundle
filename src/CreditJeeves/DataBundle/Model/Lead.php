<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class Lead
{
    /**
     * 
     * @var string
     */
    const STATUS_NEW = 'new';

    /**
     * 
     * @var string
     */
    const STATUS_ACTIVE = 'active';

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
     *
     * @ORM\Column(type="integer")
     */
    protected $cj_account_id;

    /**
     *
     * @ORM\Column(type="integer")
     */
    protected $cj_group_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $target_score;

    /**
     * @ORM\Column(type="text")
     */
    protected $target_name;

    /**
     * @ORM\Column(type="text")
     */
    protected $target_url;

    /**
     * @ORM\Column(type="integer")
     */
    protected $state;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $trade_in;

    /**
     * @ORM\Column(type="integer")
     */
    protected $down_payment;

    /**
     * @ORM\Column(type="integer")
     */
    protected $fraction;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\Column(type="string")
     */
    protected $source;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="user_leads")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     * @Assert\Type(type="CreditJeeves\DataBundle\Entity\User")
     * @Assert\Valid()
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="dealer_leads")
     * @ORM\JoinColumn(name="cj_account_id", referencedColumnName="id")
     * @Assert\Type(type="CreditJeeves\DataBundle\Entity\User")
     */
    protected $dealer;


    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Group", inversedBy="leads")
     * @ORM\JoinColumn(name="cj_group_id", referencedColumnName="id")
     * @Assert\Type(type="CreditJeeves\DataBundle\Entity\Group")
     */
    protected $group;


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
     * Set cj_account_id
     *
     * @param integer $cjAccountId
     * @return Lead
     */
    public function setCjAccountId($cjAccountId)
    {
        $this->cj_account_id = $cjAccountId;

        return $this;
    }

    /**
     * Get cj_account_id
     *
     * @return integer
     */
    public function getCjAccountId()
    {
        return $this->cj_account_id;
    }

    /**
     * Set cj_group_id
     *
     * @param integer $cjGroupId
     * @return Lead
     */
    public function setCjGroupId($cjGroupId)
    {
        $this->cj_group_id = $cjGroupId;

        return $this;
    }

    /**
     * Get cj_group_id
     *
     * @return integer
     */
    public function getCjGroupId()
    {
        return $this->cj_group_id;
    }

    /**
     * Set target_score
     *
     * @param integer $targetScore
     * @return Lead
     */
    public function setTargetScore($targetScore)
    {
        $this->target_score = $targetScore;

        return $this;
    }

    /**
     * Get target_score
     *
     * @return integer
     */
    public function getTargetScore()
    {
        return $this->target_score;
    }

    /**
     * Set target_name
     *
     * @param integer $targetName
     * @return Lead
     */
    public function setTargetName($targetName)
    {
        $this->target_name = $targetName;
    
        return $this;
    }

    /**
     * Get target_name
     *
     * @return integer
     */
    public function getTargetName()
    {
        return $this->target_name;
    }

    /**
     * Set target_url
     *
     * @param integer $targetUrl
     * @return Lead
     */
    public function setTargetUrl($targetUrl)
    {
        $this->target_url = $targetUrl;
    
        return $this;
    }

    /**
     * Get target_url
     *
     * @return integer
     */
    public function getTargetUrl()
    {
        return $this->target_url;
    }

    /**
     * Set fraction
     *
     * @param integer $fraction
     * @return Lead
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
     * @param string $status
     * @return Lead
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
     * Set source
     *
     * @param string $source
     * @return Lead
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Lead
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
     * @return Lead
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
     * Set User
     *
     * @param \CreditJeeves\DataBundle\Entity\User $user
     * @return Lead
     */
    public function setUser($user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get User
     *
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @return Lead
     */
    public function setGroup($group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set dealer
     *
     * @param \CreditJeeves\DataBundle\Entity\User $dealer
     * @return Lead
     */
    public function setDealer($dealer = null)
    {
        $this->dealer = $dealer;

        return $this;
    }

    /**
     * Get dealer
     *
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getDealer()
    {
        return $this->dealer;
    }

    /**
     * @ORM\PrePersist
     */
    public function methodPrePersist()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->status = 'new';
        $this->target_score = $this->getGroup()->getTargetScore();
    }

    /**
     * @ORM\PreUpdate
     */
    public function methodPreUpdate()
    {
        $this->updated_at = new \DateTime();
    }
}
