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
     * @ORM\ManyToOne(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *      inversedBy="imports"
     * )
     * @ORM\JoinColumn(
     *      name="group_id",
     *      referencedColumnName="id",
     *      nullable=false
     * )
     * @var \CreditJeeves\DataBundle\Entity\Group
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Landlord",
     *     inversedBy="imports"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     * @var \RentJeeves\DataBundle\Entity\Landlord
     */
    protected $user;

    /**
     * @ORM\Column(
     *     type="ImportModelType",
     *     nullable=false
     * )
     * @var string
     */
    protected $importType;

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
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @var \DateTime
     */
    protected $updatedAt;

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
    public function setCreatedAt($createdAt)
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
    public function setGroup($group)
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
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Landlord
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\Landlord $user
     */
    public function setUser($user)
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
