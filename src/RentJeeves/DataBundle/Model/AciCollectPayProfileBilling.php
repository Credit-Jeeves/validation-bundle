<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile as AciCollectPayUserProfileEntity;

/**
 * @ORM\MappedSuperclass
 */
abstract class AciCollectPayProfileBilling
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var AciCollectPayUserProfileEntity
     *
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\AciCollectPayUserProfile",
     *     inversedBy="aciCollectPayProfileBillings"
     * )
     */
    protected $profile;

    /**
     * @var string
     *
     * @ORM\Column(name="division_id", type="string", nullable=true)
     */
    protected $divisionId;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_account_number", type="string", length=20, nullable=false)
     */
    protected $billingAccountNumber;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getDivisionId()
    {
        return $this->divisionId;
    }

    /**
     * @param string $divisionId
     */
    public function setDivisionId($divisionId)
    {
        $this->divisionId = $divisionId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AciCollectPayUserProfileEntity
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param AciCollectPayUserProfileEntity $profile
     */
    public function setProfile(AciCollectPayUserProfileEntity $profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return string
     */
    public function getBillingAccountNumber()
    {
        return $this->billingAccountNumber;
    }

    /**
     * @param string $billingAccountNumber
     */
    public function setBillingAccountNumber($billingAccountNumber)
    {
        $this->billingAccountNumber = $billingAccountNumber;
    }
}
