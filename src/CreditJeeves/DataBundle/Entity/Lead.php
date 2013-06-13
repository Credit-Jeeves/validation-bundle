<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Lead as BaseLead;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\LeadRepository")
 * @ORM\Table(name="cj_lead")
 * @ORM\HasLifecycleCallbacks()
 */
class Lead extends BaseLead
{
    /**
     * @ORM\PrePersist
     */
    public function methodPrePersist()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->status = 'new';
    }
    
    public function __toString()
    {
        return (string)$this->getGroup()->getName().' '.$this->getTargetScore();
    }

    public function getGroupName()
    {
        return $this->getGroup()->getName();
    }
}
