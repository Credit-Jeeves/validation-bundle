<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\CoreBundle\Entity\cjEncryptionUtility;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_applicant_report")
 */
class Report
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
    protected $raw_data;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\UserBundle\Entity\User", inversedBy="Report")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     */
    protected $User;

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
     * @return Report
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
     * Set raw_data
     *
     * @param string $rawData
     * @return Report
     */
    public function setRawData($rawData)
    {
        $this->raw_data = $rawData;//base64_encode(cjEncryptionUtility::encrypt($rawData));//$rawData;
    
        return $this;
    }

    /**
     * Get raw_data
     *
     * @return string 
     */
    public function getRawData()
    {
      return $this->raw_data;
      $encValue = $this->raw_data;
      $value = cjEncryptionUtility::decrypt(base64_decode($encValue));
      
      return $value === false ? $encValue : $value;
    }

    /**
     * Set type_enum
     *
     * @param string $typeEnum
     * @return Report
     */
    public function setTypeEnum($typeEnum)
    {
        $this->type_enum = $typeEnum;
    
        return $this;
    }

    /**
     * Get type_enum
     *
     * @return string 
     */
    public function getTypeEnum()
    {
        return $this->type_enum;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Report
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
     * @param \CreditJeeves\UserBundle\Entity\User $user
     * @return Report
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
}