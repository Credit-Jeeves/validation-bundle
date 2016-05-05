<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
abstract class Import
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Group")
     * @ORM\JoinColumn(
     *      name="group_id",
     *      referencedColumnName="id",
     *      nullable=false
     * )
     * @var \CreditJeeves\DataBundle\Entity\Group
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\User")
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     * @var \CreditJeeves\DataBundle\Entity\User
     */
    protected $user;

    /**
     * @ORM\Column(
     *     name="import_type",
     *     type="ImportModelType",
     *     nullable=false
     * )
     * @var string
     */
    protected $importType;

    /**
     * @ORM\Column(
     *      name="error_message",
     *      type="text",
     *      nullable=true
     * )
     * @var string
     */
    protected $errorMessage;

    /**
     * @ORM\Column(
     *     type="ImportStatus",
     *     nullable=false
     * )
     * @var string
     */
    protected $status;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\ImportProperty",
     *     mappedBy="import",
     *     cascade={"persist", "merge"}
     * )
     * @var ArrayCollection|\RentJeeves\DataBundle\Entity\ImportProperty[]
     */
    protected $importProperties;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(
     *     name="finished_at",
     *     type="datetime",
     *     nullable=true
     * )
     * @var \DateTime
     */
    protected $finishedAt;

    public function __construct()
    {
        $this->importProperties = new ArrayCollection();
    }

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
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     */
    public function setGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getImportType()
    {
        return $this->importType;
    }

    /**
     * @param string $importType
     */
    public function setImportType($importType)
    {
        $this->importType = $importType;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setFinishedAt(\DateTime $updatedAt)
    {
        $this->finishedAt = $updatedAt;
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\User $user
     */
    public function setUser(\CreditJeeves\DataBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * @return ArrayCollection|\RentJeeves\DataBundle\Entity\ImportProperty[]
     */
    public function getImportProperties()
    {
        return $this->importProperties;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\ImportProperty $importProperty
     */
    public function addImportProperty(\RentJeeves\DataBundle\Entity\ImportProperty $importProperty)
    {
        $this->importProperties->add($importProperty);
    }
}
