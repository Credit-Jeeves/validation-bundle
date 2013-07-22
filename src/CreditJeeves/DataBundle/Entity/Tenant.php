<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Tenant extends User
{
    /**
     * @var string
     */
    protected $type = UserType::TETNANT;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Invite",
     *     mappedBy="tenant"
     * )
     */
    protected $invite;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     mappedBy="tenant",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     */
    protected $contracts;

    public function __construct()
    {
        parent::__construct();
        $this->contracts = new ArrayCollection();
    }

    /**
     * Set invite
     *
     * @param \RentJeeves\DataBundle\Entity\Invite $invite
     * @return Tenant
     */
    public function setInvite(\RentJeeves\DataBundle\Entity\Invite $invite = null)
    {
        $this->invite = $invite;
        return $this;
    }

    /**
     * Get invite
     *
     * @return \RentJeeves\DataBundle\Entity\Invite 
     */
    public function getInvite()
    {
        return $this->invite;
    }

    public function getTenant()
    {
        return $this;
    }

    /**
     * Add Contract
     *
     * @param Contract $contract
     * @return Tenant
     */
    public function addContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contracts[] = $contract;
        return $this;
    }

    /**
     * Remove Contract
     *
     * @param Contract
     */
    public function removeContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContracts()
    {
        return $this->contracts;
    }
}
