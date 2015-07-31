<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use CreditJeeves\DataBundle\Entity\Group;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\LandlordRepository")
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
     * @var string
     *
     * @ORM\Column(type="string", name="external_landlord_id", nullable=true)
     */
    protected $externalLandlordId;

    public function __construct()
    {
        parent::__construct();
        $this->agent_groups = new ArrayCollection();
        $this->landlordMapping = new ArrayCollection();
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
     * Return current landlord group
     *
     * @return Group|null
     */
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
        $address = $this->getAddresses()->last();

        $address || $address = $this->getDefaultAddress();

        return $address;
    }

    public function setAddress(Address $address)
    {
        $address->setUser($this); // TODO: hack, need check
        $this->addAddress($address);
    }

    public function hasMerchant()
    {
        $groups = $this->getGroups();
        if (!$groups) {
            return false;
        }

        $merchantExist = false;
        /** @var Group $group */
        foreach ($groups as $group) {
            if (($depositAccount = $group->getRentDepositAccountForCurrentPaymentProcessor()) &&
                $depositAccount->isComplete()
            ) {
                $merchantExist = true;
                break;
            }
        }

        return $merchantExist;
    }

    /**
     * @return string
     */
    public function getExternalLandlordId()
    {
        return $this->externalLandlordId;
    }

    /**
     * @param string $externalLandlordId
     */
    public function setExternalLandlordId($externalLandlordId)
    {
        $this->externalLandlordId = $externalLandlordId;
    }
}
