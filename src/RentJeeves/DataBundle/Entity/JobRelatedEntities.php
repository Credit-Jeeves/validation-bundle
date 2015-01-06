<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name = "jms_job_related_entities")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="related_class", type="string")
 * @ORM\DiscriminatorMap({
 *      "payment" = "JobRelatedPayment",
 *      "order" = "JobRelatedOrder",
 *      "credit_track" = "JobRelatedCreditTrack",
 *      "report" = "JobRelatedReport"
 * })
 */
class JobRelatedEntities
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "AUTO")
     * @ORM\Column(type = "bigint", options = {"unsigned": true})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity = "Job",
     *      inversedBy = "relatedEntities",
     *      cascade = {"persist", "remove", "detach"},
     *      fetch = "EAGER"
     * )
     * @ORM\JoinColumn(name="job_id", referencedColumnName="id")
     * @var Job
     */
    protected $job;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     * @var DateTime
     */
    protected $createdAt;

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function __toString()
    {
        return (string)$this->job;
    }

    public function getJob()
    {
        return $this->job;
    }
}
