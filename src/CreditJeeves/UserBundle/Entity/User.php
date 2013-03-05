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
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Report", mappedBy="User")
     */
    protected $Report;

    /**
     * @ORM\OneToMany(targetEntity="CreditJeeves\DataBundle\Entity\Score", mappedBy="User")
     */
    protected $Score;

    public function __construct()
    {
        parent::__construct();
        $this->Report = new ArrayCollection();
        $this->Score = new ArrayCollection();
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
     * Add Report
     *
     * @param \CreditJeeves\DataBundle\Entity\Report $report
     * @return User
     */
    public function addReport(\CreditJeeves\DataBundle\Entity\Report $report)
    {
        $this->Report[] = $report;
    
        return $this;
    }

    /**
     * Remove Report
     *
     * @param \CreditJeeves\DataBundle\Entity\Report $report
     */
    public function removeReport(\CreditJeeves\DataBundle\Entity\Report $report)
    {
        $this->Report->removeElement($report);
    }

    /**
     * Get Report
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReport()
    {
        return $this->Report;
    }

    /**
     * Add Score
     *
     * @param \CreditJeeves\DataBundle\Entity\Score $score
     * @return User
     */
    public function addScore(\CreditJeeves\DataBundle\Entity\Score $score)
    {
        $this->Score[] = $score;
    
        return $this;
    }

    /**
     * Remove Score
     *
     * @param \CreditJeeves\DataBundle\Entity\Score $score
     */
    public function removeScore(\CreditJeeves\DataBundle\Entity\Score $score)
    {
        $this->Score->removeElement($score);
    }

    /**
     * Get Score
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getScore()
    {
        return $this->Score;
    }
}