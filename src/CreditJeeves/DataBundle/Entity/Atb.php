<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Atb as BaseAtb;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\AtbRepository")
 * @ORM\Table(name="atb_simulation")
 * @ORM\HasLifecycleCallbacks()
 */
class Atb extends BaseAtb
{
    /**
     * @ORM\PreRemove
     */
    public function methodPreRemove()
    {
    }

    /**
     * @ORM\PostRemove
     */
    public function methodPostRemove()
    {
    }

    /**
     * @ORM\PrePersist
     */
    public function methodPrePersist()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PostPersist
     */
    public function methodPostPersist()
    {
    }

    /**
     * @ORM\PreUpdate
     */
    public function methodPreUpdate()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PostUpdate
     */
    public function methodPostUpdate()
    {
    }

    /**
     * @ORM\PostLoad
     */
    public function methodPostLoad()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setResult($result)
    {
        $this->setSimType($result['sim_type']);
        $this->setTransactionSignature($result['transaction_signature']);
        parent::setResult(serialize($result));
    }

    /**
     * Get result
     *
     * @return array
     */
    public function getResult()
    {
        return unserialize(parent::getResult());
    }
}
