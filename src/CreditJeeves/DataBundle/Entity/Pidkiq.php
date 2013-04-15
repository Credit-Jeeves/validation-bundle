<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_applicant_pidkiq")
 * @ORM\HasLifecycleCallbacks()
 */
class Pidkiq
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
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User", inversedBy="scores")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $questions;

    /**
     * @ORM\Column(type="integer")
     */
    protected $try_num;

    /**
     * @ORM\Column(type="string")
     */
    protected $session_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $check_summ;

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
     * Set try_num
     *
     * @param integer $tryNum
     * @return Pidkiq
     */
    public function setTryNum($tryNum)
    {
        $this->try_num = $tryNum;
    
        return $this;
    }

    /**
     * Get try_num
     *
     * @return integer 
     */
    public function getTryNum()
    {
        return $this->try_num;
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
    public function getCheckSumm()
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
}
