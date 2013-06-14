<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Lead as BaseLead;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\LeadRepository")
 * @ORM\Table(name="cj_lead")
 * @ORM\HasLifecycleCallbacks()
 *
 * @Gedmo\Loggable(logEntryClass="CreditJeeves\DataBundle\Entity\LeadHistory")
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
}
