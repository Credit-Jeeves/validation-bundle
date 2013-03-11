<?php
namespace CreditJeeves\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use CreditJeeves\DataBundle\Entity\Report;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\ReportPrequal", mappedBy="user")
     */
    protected $reportsPrequal;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\ReportD2c", mappedBy="user")
     */
    protected $reportsD2c;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Score", mappedBy="user")
     */
    protected $scores;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Lead", mappedBy="user")
     */
    protected $leads;

    /**
     * 
     * @var string
     */
    protected $new_password;

    public function __construct()
    {
        parent::__construct();
        $this->reportsPrequal = new ArrayCollection();
        $this->reportsD2c     = new ArrayCollection();
        $this->scores         = new ArrayCollection();
        $this->leads          = new ArrayCollection();
    }

    public function getNewPassword()
    {
        return $this->new_password;
    }
    

    public function setNewPassword($newPassword)
    {
        $this->new_password = $newPassword;
    }

  /**
   * (non-PHPdoc)
   * @see FOS\UserBundle\Model.User::setPassword()
   */
  public function setPassword($password)
  {
    $this->password = md5($password);
  }

  public function getType()
  {
    return $this->type;
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
     * Set type
     *
     * @param string $type
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }


    /**
     * Add scores
     *
     * @param \CreditJeeves\DataBundle\Entity\Score $scores
     * @return User
     */
    public function addScore(\CreditJeeves\DataBundle\Entity\Score $scores)
    {
        $this->scores[] = $scores;
    
        return $this;
    }

    /**
     * Remove scores
     *
     * @param \CreditJeeves\DataBundle\Entity\Score $scores
     */
    public function removeScore(\CreditJeeves\DataBundle\Entity\Score $scores)
    {
        $this->scores->removeElement($scores);
    }

    /**
     * Get scores
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getScores()
    {
        return $this->scores;
    }

    /**
     * Add leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $leads
     * @return User
     */
    public function addLead(\CreditJeeves\DataBundle\Entity\Lead $leads)
    {
        $this->leads[] = $leads;
    
        return $this;
    }

    /**
     * Remove leads
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $leads
     */
    public function removeLead(\CreditJeeves\DataBundle\Entity\Lead $leads)
    {
        $this->leads->removeElement($leads);
    }

    /**
     * Get leads
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLeads()
    {
        return $this->leads;
    }

    /**
     * Add reportsPrequal
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal
     * @return User
     */
    public function addReportsPrequal(\CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal)
    {
        $this->reportsPrequal[] = $reportsPrequal;
    
        return $this;
    }

    /**
     * Remove reportsPrequal
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal
     */
    public function removeReportsPrequal(\CreditJeeves\DataBundle\Entity\ReportPrequal $reportsPrequal)
    {
        $this->reportsPrequal->removeElement($reportsPrequal);
    }

    /**
     * Get reportsPrequal
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReportsPrequal()
    {
        return $this->reportsPrequal;
    }

    /**
     * Add reportsD2c
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c
     * @return User
     */
    public function addReportsD2c(\CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c)
    {
        $this->reportsD2c[] = $reportsD2c;
    
        return $this;
    }

    /**
     * Remove reportsD2c
     *
     * @param \CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c
     */
    public function removeReportsD2c(\CreditJeeves\DataBundle\Entity\ReportD2c $reportsD2c)
    {
        $this->reportsD2c->removeElement($reportsD2c);
    }

    /**
     * Get reportsD2c
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReportsD2c()
    {
        return $this->reportsD2c;
    }
}