<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks()
 */
class UserRepository extends EntityRepository
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
}
