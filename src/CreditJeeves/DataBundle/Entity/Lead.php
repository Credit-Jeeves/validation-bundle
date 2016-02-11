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
 */
class Lead extends BaseLead
{
    /**
     * @ORM\PrePersist
     */
    public function methodPrePersist()
    {
        if (!$this->getTargetScore()) {
            $this->setTargetScore($this->getGroup() ? $this->getGroup()->getTargetScore() : null);
        }
    }

    public function __toString()
    {
        return (string)$this->getGroupName().' '.$this->getTargetScore();
    }

    public function getGroupName()
    {
        $group = $this->getGroup();
        return $group ? $group->getName() : '';
    }

    public function getCurrentScore()
    {
        return $this->getUser()->getCurrentScore();
    }

    /**
     * Moved from symfony1
     *
     * /vendor/credit-jeeves/credit-jeeves/lib/model/doctrine/cjLead.class.php
     *
     * @param integer $currentScore
     */
    public function setNewFraction($currentScore)
    {
        $currentScore = intval($currentScore);
        $targetScore = $this->getTargetScore();
        $fraction = floor($currentScore * 100 / $targetScore);
        $this->setFraction($fraction);
    }
}
