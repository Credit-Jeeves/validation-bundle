<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\CoreBundle\Utility\Encryption;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_settings")
 * @ORM\HasLifecycleCallbacks()
 */
class Settings
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $pidkiq_password;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $pidkiq_eai;

    /**
     * 
     * @ORM\Column(type="encrypt")
     */
    protected $net_connect_password;

    /**
     * 
     * @ORM\Column(type="encrypt")
     */
    protected $net_connect_eai;

    /**
     * 
     * @ORM\Column(type="text")
     */
    protected $contract;

    /**
     * 
     * @ORM\Column(type="text")
     */
    protected $rights;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

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
     * Set score
     *
     * @param string $score
     * @return Score
     */
    public function setPidkiqPassword($password)
    {
        $this->pidkiq_password = $password;
    
        return $this;
    }

    /**
     * Get score
     *
     * @return string 
     */
    public function getPidkiqPassword()
    {
        return $this->pidkiq_password;
    }

    public function setRights($rights)
    {
      $this->rights = $rights;
      return $this;
    }

    public function getRights()
    {
      return $this->rights;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\preUpdate
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime();
    }
}
