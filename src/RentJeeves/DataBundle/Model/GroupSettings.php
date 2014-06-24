<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\CoreBundle\DateTime;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class GroupSettings
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="bigint"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(
     *     name="pid_verification",
     *     type="boolean"
     * )
     * @var boolean
     */
    protected $isPidVerificationSkipped = false;

    /**
     * @ORM\Column(
     *      type="boolean",
     *      name="is_integrated",
     *      options={
     *          "default":0
     *      }
     * )
     */
    protected $isIntegrated = false;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="groupSettings",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false, unique=true)
     * @var Group
     */
    protected $group;

    /**
     * @ORM\Column(
     *      type="integer",
     *      name="due_date",
     *      options={
     *          "default":1
     *      },
     *      nullable=false
     * )
     */
    protected $dueDate = 1;

    /**
     * @ORM\Column(
     *      type="integer",
     *      name="open_date",
     *      options={
     *          "default":1
     *      },
     *      nullable=false
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $openDate = 1;

    /**
     * @ORM\Column(
     *      type="integer",
     *      name="close_date",
     *      options={
     *          "default":31
     *      },
     *      nullable=false
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $closeDate = 31;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * @param boolean $pidVerification
     */
    public function setIsPidVerificationSkipped($pidVerification)
    {
        $this->isPidVerificationSkipped = (boolean) $pidVerification;
    }

    /**
     * @return boolean
     */
    public function getIsPidVerificationSkipped()
    {
        return $this->isPidVerificationSkipped;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param boolean $isIntegrated
     */
    public function setIsIntegrated($isIntegrated)
    {
        $this->isIntegrated = (boolean) $isIntegrated;
    }

    /**
     * @return boolean
     */
    public function getIsIntegrated()
    {
        return $this->isIntegrated;
    }

    /**
     * @param integer $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return integer
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param integer $openDate
     */
    public function setOpenDate($openDate)
    {
        $this->openDate = $openDate;
    }

    /**
     * @return integer
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * @param integer $closeDate
     */
    public function setCloseDate($closeDate)
    {
        $this->closeDate = $closeDate;
    }

    /**
     * @return integer
     */
    public function getCloseDate()
    {
        return $this->closeDate;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
