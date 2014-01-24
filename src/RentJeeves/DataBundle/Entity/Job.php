<?php
namespace RentJeeves\DataBundle\Entity;

use JMS\JobQueueBundle\Entity\Job as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass = "RentJeeves\DataBundle\Entity\JobRepository")
 * @ORM\Table(name = "jms_jobs", indexes = {
 *     @ORM\Index(columns = {"command"}),
 *     @ORM\Index("job_runner", columns = {"executeAfter", "state"}),
 * })
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Job extends Base
{
    /**
     * @ORM\ManyToOne(targetEntity = "Job", inversedBy = "retryJobs")
     * @ORM\JoinColumn(name="originalJob_id", referencedColumnName="id")
     */
    protected $originalJob;

    /**
     * @ORM\OneToMany(targetEntity = "Job", mappedBy = "originalJob", cascade = {"persist", "remove", "detach"})
     */
    protected $retryJobs;


    /**
     * @ORM\OneToMany(targetEntity = "JobRelatedEntities", mappedBy = "job", cascade = {"persist", "remove", "detach"})
     */
    protected $relatedEntities;

    /**
     * @ORM\ManyToMany(targetEntity = "Job", fetch = "EAGER")
     * @ORM\JoinTable(name="jms_job_dependencies",
     *     joinColumns = { @ORM\JoinColumn(name = "source_job_id", referencedColumnName = "id") },
     *     inverseJoinColumns = { @ORM\JoinColumn(name = "dest_job_id", referencedColumnName = "id")}
     * )
     */
    protected $dependencies;
}
