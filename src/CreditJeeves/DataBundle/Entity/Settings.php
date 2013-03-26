<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\CoreBundle\Utility\Encryption;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_settings")
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
     * @ORM\Column(type="text")
     */
    protected $pidkiq_password;

    /**
     * @ORM\Column(type="text")
     */
    protected $pidkiq_eai;

    /**
     * 
     * @ORM\Column(type="text")
     */
    protected $net_connect_password;

    /**
     * 
     * @ORM\Column(type="text")
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
        $Utility = new Encryption();
        $this->pidkiq_password = base64_encode(\cjEncryptionUtility::encrypt($password));
    
        return $this;
    }

    /**
     * Get score
     *
     * @return string 
     */
    public function getPidkiqPassword()
    {
        $Utility = new Encryption();
        $encValue = $this->pidkiq_password;
        $value = \cjEncryptionUtility::decrypt(base64_decode($encValue));
        
        return $value === false ? $encValue : $value;
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
}
