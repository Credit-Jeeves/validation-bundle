<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\Report;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\JobQueueBundle\Entity\Job as Base;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;

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
     * @var ArrayCollection
     * @ORM\OneToMany(
     *      targetEntity = "JobRelatedEntities",
     *      mappedBy = "job",
     *      cascade = {"persist", "remove", "detach"},
     *      fetch = "EAGER"
     * )
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

    public function __construct($command = '', array $args = [], $confirmed = true)
    {
        if (false === in_array('--app=rj', $args)) {
            $args[] = '--app=rj';
        }

        parent::__construct($command, $args, $confirmed);
    }

    public function setRelatedEntities(ArrayCollection $relatedEntities)
    {
        $this->relatedEntities = $relatedEntities;
    }

    public function __clone()
    {
        parent::__clone();
        if ($re = $this->getRelatedEntities()) {
            $clones = new ArrayCollection();
            foreach ($re->getIterator() as $rel) {
                $clone = clone $rel;
                $rel->setJob($this);
                $clones->add($rel);
            }
            $this->setRelatedEntities($clones);
        }
    }

    public function addRelatedEntity($entity)
    {
        assert('is_object($entity)');

        switch (true) {
            case $entity instanceof Payment:
                $jobRelated = new JobRelatedPayment();
                $jobRelated->setPayment($entity);
                break;
            case $entity instanceof Order:
                $jobRelated = new JobRelatedOrder();
                $jobRelated->setOrder($entity);
                break;
            case $entity instanceof Report:
                $jobRelated = new JobRelatedReport();
                $jobRelated->setReport($entity);
                break;
            case $entity instanceof JobRelatedEntities:
                $jobRelated = $entity;
                break;
            default:
                throw new RuntimeException(sprintf("Provided entity '%s' does not registered", get_class($entity)));

        }
        $jobRelated->setJob($this);

        $this->relatedEntities->add($jobRelated);
    }

    public function setStateRaw($state)
    {
        $this->state = $state;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;
    }

    public function setCheckedAt($checkedAt)
    {
        $this->checkedAt = $checkedAt;
    }

    public function setClosedAt($closedAt)
    {
        $this->closedAt = $closedAt;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }

    public function setArgs($args)
    {
        $this->args = $args;
    }
}
