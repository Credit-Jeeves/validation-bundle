<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Pidkiq as BasePidkiq;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\PidkiqRepository")
 * @ORM\Table(name="cj_applicant_pidkiq")
 * @ORM\HasLifecycleCallbacks()
 */
class Pidkiq extends BasePidkiq
{
    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function setQuestions($q)
    {
        parent::setQuestions(serialize($q));
    }

    public function getQuestions()
    {
        return unserialize(parent::getQuestions());
    }
}
