<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
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
    protected $publicId;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="type",
     *     type="ImportType",
     *     nullable=false
     * )
     */
    protected $type;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="imports"
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
    protected $countTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_new", type="integer")
     */
    protected $countNew;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_match", type="integer")
     */
    protected $countMatch;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_invite", type="integer")
     */
    protected $countInvite;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_skipped", type="integer")
     */
    protected $countSkipped;

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
    public function getCountInvite()
    {
        return $this->countInvite;
    }

    /**
     * @param int $countInvite
     */
    public function setCountInvite($countInvite)
    {
        $this->countInvite = $countInvite;
    }

    /**
     * @return int
     */
    public function getCountMatch()
    {
        return $this->countMatch;
    }

    /**
     * @param int $countMatch
     */
    public function setCountMatch($countMatch)
    {
        $this->countMatch = $countMatch;
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
