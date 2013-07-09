<?php
namespace CreditJeeves\DataBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Report
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
     *     inversedBy="report"
     * )
     * @ORM\JoinColumn(
     *     name="cj_applicant_id",
     *     referencedColumnName="id"
     * )
     */
    protected $user;


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
     * Set raw_data
     *
     * @param string $rawData
     * @return Report
     */
    public function setRawData($rawData)
    {
        $this->raw_data = $rawData;
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
     * @return User
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
}
