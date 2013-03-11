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
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Report", mappedBy="user")
     */
    protected $reports;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Score", mappedBy="user")
     */
    protected $scores;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Lead", mappedBy="user")
     */
    protected $leads;

    public function __construct()
    {
        parent::__construct();
        $this->reports = new ArrayCollection();
        $this->scores  = new ArrayCollection();
        $this->leads   = new ArrayCollection();
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
     * Add reports
     *
     * @param \CreditJeeves\DataBundle\Entity\Report $reports
     * @return User
     */
    public function addReport(\CreditJeeves\DataBundle\Entity\Report $reports)
    {
        $this->reports[] = $reports;
    
        return $this;
    }

    /**
     * Remove reports
     *
     * @param \CreditJeeves\DataBundle\Entity\Report $reports
     */
    public function removeReport(\CreditJeeves\DataBundle\Entity\Report $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReports()
    {
        return $this->reports;
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
}