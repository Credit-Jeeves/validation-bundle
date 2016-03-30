<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
abstract class TrustedLandlord
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CheckMailingAddress
     *
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\CheckMailingAddress",
     *     inversedBy="trustedLandlord",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     *
     * @ORM\JoinColumn(
     *     name="check_mailing_address_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected $checkMailingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name",type="string",length=255,nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name",type="string",length=255,nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name",type="string",length=255,nullable=true)
     */
    protected $companyName;

    /**
     * @var string
     *
     * @ORM\Column(type="TrustedLandlordType")
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="TrustedLandlordStatus")
     */
    protected $status;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(name="created_at",type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(name="updated_at",type="datetime")
     */
    protected $updatedAt;

    /**
     * @var TrustedLandlordJiraMapping
     *
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping",
     *     mappedBy="trustedLandlord",
     *     cascade={"persist"}
     * )
     */
    protected $jiraMapping;

    /**
     * @var Group
     *
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     mappedBy="trustedLandlord",
     *     cascade={"persist"}
     * )
     */
    protected $group;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\CheckMailingAddress $checkMailingAddress
     */
    public function setCheckMailingAddress(\RentJeeves\DataBundle\Entity\CheckMailingAddress $checkMailingAddress)
    {
        $this->checkMailingAddress = $checkMailingAddress;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\CheckMailingAddress
     */
    public function getCheckMailingAddress()
    {
        return $this->checkMailingAddress;
    }

    /**
     * @return TrustedLandlordJiraMapping
     */
    public function getJiraMapping()
    {
        return $this->jiraMapping;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping $jiraMapping
     */
    public function setJiraMapping(\RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping $jiraMapping)
    {
        $this->jiraMapping = $jiraMapping;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }
}
