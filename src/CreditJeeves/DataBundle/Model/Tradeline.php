<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Tradeline
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
    protected $cj_group_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\Column(type="text")
     */
    protected $tradeline;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_fixed;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_disputed;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_completed;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     inversedBy="tradelines"
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
     * @return Tradeline
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
     * Set cj_group_id
     *
     * @param integer $cjGrouptId
     * @return Tradeline
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
     * Set tradeline
     *
     * @param string $tradeline
     * @return Tradeline
     */
    public function setTradeline($tradeline)
    {
        $this->tradeline = $tradeline;
        return $this;
    }

    /**
     * Get tradeline
     *
     * @return string
     */
    public function getTradeline()
    {
        return $this->tradeline;
    }

    /**
     * Set is_fixed
     *
     * @param boolean $isFixed
     * @return Tradeline
     */
    public function setIsFixed($isFixed)
    {
        $this->is_fixed = $isFixed;
        return $this;
    }

    /**
     * Get is_fixed
     *
     * @return boolean
     */
    public function getIsFixed()
    {
        return $this->is_fixed;
    }

    /**
     * Set is_disputed
     *
     * @param boolean $isDisputed
     * @return Tradeline
     */
    public function setIsDisputed($isDisputed)
    {
        $this->is_disputed = $isDisputed;
        return $this;
    }

    /**
     * Get is_disputed
     *
     * @return boolean
     */
    public function getIsDisputed()
    {
        return $this->is_disputed;
    }

    /**
     * Set is_completed
     *
     * @param boolean $isCompleted
     * @return Tradeline
     */
    public function setIsCompleted($isCompleted)
    {
        $this->is_completed = $isCompleted;
        return $this;
    }

    /**
     * Get is_completed
     *
     * @return boolean
     */
    public function getIsCompleted()
    {
        return $this->is_completed;
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
     * Set updated_date
     *
     * @param \DateTime $updatedAt
     * @return Tradeline
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
