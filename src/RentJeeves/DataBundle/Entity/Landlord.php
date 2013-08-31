<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\JoinTable(
     *      name="rj_permission",
     *      joinColumns={@ORM\JoinColumn(name="agent_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $agent_groups;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\DepositAccount",
     *     mappedBy="user",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    protected $payment_accounts;
    
    public function __construct()
    {
        parent::__construct();
        $this->agent_groups = new ArrayCollection();
        $this->payment_accounts = new ArrayCollection();
    }
    
    /**
     * Add groups
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $groups
     * @return \RentJeeves\DataBundle\Entity\Landlord
     */
    public function setAgentGroups($groups)
    {
        if (is_object($groups)) {
            $this->addAgentGroup($groups);
        }
        foreach ($groups as $group) {
            $this->addAgentGroup($group);
        }
        return $this;
    }

    /**
     * Add group
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @return \RentJeeves\DataBundle\Entity\Landlord
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

    /**
     * Add account
     *
     * @param \RentJeeves\DataBundle\Entity\DepositAccount $account
     * @return \RentJeeves\DataBundle\Entity\Landlord
     */
    public function addPaymentAccount(\RentJeeves\DataBundle\Entity\DepositAccount $account)
    {
        $this->payment_accounts[] = $account;
        return $this;
    }

    /**
     * Remove account
     *
     * @param \RentJeeves\DataBundle\Entity\DepositAccount $account
     */
    public function removePaymentAccount(\RentJeeves\DataBundle\Entity\DepositAccount $account)
    {
        $this->payment_accounts->removeElement($account);
    }

    /**
     * Get payment_accounts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPaymentAccounts()
    {
        return $this->payment_accounts;
    }

    public function getCurrentGroup()
    {
        if ($isAdmin = $this->getIsSuperAdmin()) {
            $holding = $this->getHolding();
            $groups = $holding->getGroups() ? $holding->getGroups() : null;
        } else {
            $groups = $this->getAgentGroups() ? $this->getAgentGroups() : null;
        }

        if ($groups) {
            return $groups->first();
        }

        return null;
    }

    public function getGroups()
    {
        if ($isAdmin = $this->getIsSuperAdmin()) {
            $holding = $this->getHolding();
            return $holding->getGroups() ? $holding->getGroups() : null;
        } else {
            return $this->getAgentGroups() ? $this->getAgentGroups() : null;
        }
    }

    public function getAddress()
    {
        return $this->getAddresses()->last();
    }
}
