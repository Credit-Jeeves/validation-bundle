<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\Tenant;

/**
 * @ORM\MappedSuperclass
 */
abstract class AciImportProfileMap
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Tenant",
     *     inversedBy="aciImportProfileMap"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     *
     * @var Tenant
     */
    protected $user;

    /**
     * @ORM\OneToOne(
     *      targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *      inversedBy="aciImportProfileMap"
     * )
     * @ORM\JoinColumn(
     *      name="group_id",
     *      referencedColumnName="id"
     * )
     *
     * @var Group
     */
    protected $group;

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Tenant
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Tenant $user
     */
    public function setUser(Tenant $user)
    {
        $this->user = $user;
    }
}
