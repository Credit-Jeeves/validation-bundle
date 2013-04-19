<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class ApplicantIncentive
{
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
     * @ORM\Column(type="integer")
     */
    protected $cj_tradeline_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $cj_incentive_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_verified;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     inversedBy="incentives"
     * )
     * @ORM\JoinColumn(
     *     name="cj_applicant_id",
     *     referencedColumnName="id"
     * )
     */
    protected $user;

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
     * @return ApplicantIncentive
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
     * Set cj_tradeline_id
     *
     * @param integer $cjTradelineId
     * @return ApplicantIncentive
     */
    public function setCjTradelineId($cjTradelineId)
    {
        $this->cj_tradeline_id = $cjTradelineId;
        return $this;
    }

    /**
     * Get cj_tradeline_id
     *
     * @return integer
     */
    public function getCjTradelineId()
    {
        return $this->cj_tradeline_id;
    }

    /**
     * Set status
     * 
     * @param string $status
     * @return Tradeline
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
     * Set is_verified
     *
     * @param boolean $isVerified
     * @return Tradeline
     */
    public function setIsVerified($isVerified)
    {
        $this->is_verified = $isVerified;
        return $this;
    }
    
    /**
     * Get is_disputed
     *
     * @return boolean
     */
    public function getIsVerified()
    {
        return $this->is_verified;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Score
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
     * Set User
     *
     * @param User $user
     * @return Tradeline
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get User
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
