<?php
namespace CreditJeeves\DataBundle\Model;

use CreditJeeves\SimulationBundle\Model\BaseReport;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
abstract class Report extends BaseReport
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $cj_applicant_id;

    /**
     * @ORM\Column(type="encrypt")
     */
    protected $raw_data;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Atb",
     *     mappedBy="report",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $atbs;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\User",
     *     inversedBy="reports"
     * )
     * @ORM\JoinColumn(
     *     name="cj_applicant_id",
     *     referencedColumnName="id"
     * )
     */
    protected $user;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\JobRelatedReport",
     *     mappedBy="report",
     *     cascade={"persist", "merge"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    protected $jobs;

    public function __construct()
    {
        $this->atbs = new ArrayCollection();
    }

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
     * Add atbs
     *
     * @param \CreditJeeves\DataBundle\Entity\Atb $atb
     * @return Report
     */
    public function addAtbs(\CreditJeeves\DataBundle\Entity\Atb $atb)
    {
        $this->atbs[] = $atb;
        return $this;
    }

    /**
     * Remove atbs
     *
     * @param \CreditJeeves\DataBundle\Entity\Atb $atb
     */
    public function removeAtbs(\CreditJeeves\DataBundle\Entity\Atb $atb)
    {
        $this->atbs->removeElement($atb);
    }

    /**
     * Get atbs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAtbs()
    {
        return $this->atbs;
    }

    /**
     * Set user
     *
     * @param \CreditJeeves\DataBundle\Entity\User $user
     * @return Report
     */
    public function setUser(\CreditJeeves\DataBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return \CreditJeeves\DataBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
