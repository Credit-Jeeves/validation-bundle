<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Enum\PidkiqStatus;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Pidkiq
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
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="pidkiqs")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="encrypt", nullable=true)
     */
    protected $questions;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $session_id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $check_summ;

    /**
     * @ORM\Column(type="PidkiqStatus", nullable=false)
     */
    protected $status = PidkiqStatus::UNABLE;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime();
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
     * @return Pidkiq
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
     * Set questions
     *
     * @param encrypt $questions
     * @return Pidkiq
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;

        return $this;
    }

    /**
     * Get questions
     *
     * @return encrypt
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Pidkiq
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
     * Set session_id
     *
     * @param string $sessionId
     * @return Pidkiq
     */
    public function setSessionId($sessionId)
    {
        $this->session_id = $sessionId;

        return $this;
    }

    /**
     * Get session_id
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * Set check_summ
     *
     * @param string $checkSumm
     * @return Pidkiq
     */
    public function setCheckSumm($checkSumm)
    {
        $this->check_summ = $checkSumm;

        return $this;
    }

    /**
     * Get check_summ
     *
     * @return string
     */
    public function getCheckSum()
    {
        return $this->check_summ;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Pidkiq
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
     * @param User $user
     * @return Pidkiq
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $status
     * @see PidkiqStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     * @see PidkiqStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
