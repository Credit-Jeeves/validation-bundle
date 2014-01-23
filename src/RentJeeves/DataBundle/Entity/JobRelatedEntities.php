<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name = "jms_job_related_entities")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="related_class", type="string")
 * @ORM\DiscriminatorMap({"payment" = "JobRelatedPayment", "order" = "JobRelatedOrder"})
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
     * @ORM\ManyToOne(targetEntity = "Job", inversedBy = "relatedEntities")
     * @ORM\JoinColumn(name="job_id", referencedColumnName="id")
     * @var Job
     */
    protected $job;

//    protected $relatedClass;

//    protected $relatedId;

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }
}
