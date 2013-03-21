<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\CoreBundle\Utility\Encryption;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_applicant_score")
 */
class Score
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
     * @ORM\Column(type="text")
     */
    protected $score;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_date;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\UserBundle\Entity\User", inversedBy="scores")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
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
     * @return Score
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
     * Set score
     *
     * @param string $score
     * @return Score
     */
    public function setScore($score)
    {
        $Utility = new Encryption();
        $this->score = base64_encode(\cjEncryptionUtility::encrypt($score));
    
        return $this;
    }

    /**
     * Get score
     *
     * @return string 
     */
    public function getScore()
    {
        $Utility = new Encryption();
        $encValue = $this->score;
        $value = \cjEncryptionUtility::decrypt(base64_decode($encValue));
        
        return $value === false ? $encValue : $value;
    }

    public function getFicoScore()
    {
        $nScore = $this->getScore();
        $nFicoScore = round(10 * (($nScore - 483.06) / 11.079) + 490);
        return $nFicoScore > 850 ? 850 : $nFicoScore;
    }

    /**
     * Set User
     *
     * @param \CreditJeeves\UserBundle\Entity\User $user
     * @return Score
     */
    public function setUser(\CreditJeeves\UserBundle\Entity\User $user = null)
    {
        $this->User = $user;
    
        return $this;
    }

    /**
     * Get User
     *
     * @return \CreditJeeves\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Set created_date
     *
     * @param \DateTime $createdDate
     * @return Score
     */
    public function setCreatedDate($createdDate)
    {
        $this->created_date = $createdDate;
    
        return $this;
    }

    /**
     * Get created_date
     *
     * @return \DateTime 
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * @return string
     */
    public function getScorePercentage()
    {
        $score = $this->getScore();
        if ($score >= 900) {
            return "84%";
        }
        if ($score >= 800) {
            return "64%";
        }
        if ($score >= 700) {
            return "44%";
        }
        if ($score >= 600) {
            return "19%";
        }
        if ($score >= 550) {
            return "7%";
        }
        return "2%";
    }
}
