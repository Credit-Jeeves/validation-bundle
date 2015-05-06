<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Enum\ImportType;

/**
 * @ORM\MappedSuperclass
 */
abstract class ImportSummary
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="public_id",
     *      type="integer",
     *      nullable=true
     * )
     */
    protected $publicId = 0;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="type",
     *     type="ImportType",
     *     nullable=false
     * )
     */
    protected $type = ImportType::SINGLE_PROPERTY;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="importSummaries"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     */
    protected $group;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\ImportError",
     *     mappedBy="import",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     */
    protected $errors;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_total", type="integer")
     */
    protected $countTotal = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_new", type="integer")
     */
    protected $countNew = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_matched", type="integer")
     */
    protected $countMatched = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_invited", type="integer")
     */
    protected $countInvited = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_skipped", type="integer")
     */
    protected $countSkipped = 0;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getCountInvited()
    {
        return $this->countInvited;
    }

    /**
     * @param int $countInvited
     */
    public function setCountInvited($countInvited)
    {
        $this->countInvited = $countInvited;
    }

    /**
     * @return int
     */
    public function getCountMatched()
    {
        return $this->countMatched;
    }

    /**
     * @param int $countMatched
     */
    public function setCountMatched($countMatched)
    {
        $this->countMatched = $countMatched;
    }

    /**
     * @return int
     */
    public function getCountNew()
    {
        return $this->countNew;
    }

    /**
     * @param int $countNew
     */
    public function setCountNew($countNew)
    {
        $this->countNew = $countNew;
    }

    /**
     * @return int
     */
    public function getCountSkipped()
    {
        return $this->countSkipped;
    }

    /**
     * @param int $countSkipped
     */
    public function setCountSkipped($countSkipped)
    {
        $this->countSkipped = $countSkipped;
    }

    /**
     * @return int
     */
    public function getCountTotal()
    {
        return $this->countTotal;
    }

    /**
     * @param int $countTotal
     */
    public function setCountTotal($countTotal)
    {
        $this->countTotal = $countTotal;
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPublicId()
    {
        return $this->publicId;
    }

    /**
     * @param int $publicId
     */
    public function setPublicId($publicId)
    {
        $this->publicId = $publicId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
