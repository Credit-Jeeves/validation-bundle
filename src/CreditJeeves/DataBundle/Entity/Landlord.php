<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Landlord extends User
{
    /**
     * @var string
     */
    protected $type = UserType::LANDLORD;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="group_agents"
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     * @ORM\JoinTable(
     *      name="rj_permission",
     *      joinColumns={@ORM\JoinColumn(name="agent_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $agent_groups;

    /**
     * Add group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @return Landlord
     */
    public function addAgentGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->agent_groups[] = $group;
        return $this;
    }

    /**
     * Remove group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     */
    public function removeAgentGroup(\CreditJeeves\DataBundle\Entity\Group $group)
    {
        $this->agent_groups->removeElement($group);
    }

    /**
     * Get agent_groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAgentGroups()
    {
        return $this->agent_groups;
    }
}
