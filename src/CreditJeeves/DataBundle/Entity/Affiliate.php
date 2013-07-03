<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Affiliate as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\AffiliateRepository")
 * @ORM\Table(name="cj_affiliate")
 * @ORM\HasLifecycleCallbacks()
 */
class Affiliate extends Base
{

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->getName()?:'';
    }
}
