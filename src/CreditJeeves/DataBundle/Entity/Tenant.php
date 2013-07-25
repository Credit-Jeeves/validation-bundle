<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\TenantRepository")
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

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Payment",
     *     mappedBy="tenant",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     */
    protected $payments;
    

    public function __construct()
    {
        parent::__construct();
        $this->contracts = new ArrayCollection();
        $this->payments = new ArrayCollection();
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

    /**
     * Add payment
     *
     * @param \RentJeeves\DataBundle\Entity\Payment $payment
     * @return Tenant
     */
    public function addPayment(\RentJeeves\DataBundle\Entity\Payment $payment)
    {
        $this->payments[] = $payment;
        return $this;
    }

    /**
     * Remove paymnet
     *
     * @param \RentJeeves\DataBundle\Entity\Payment $payment
     */
    public function removePayment(\RentJeeves\DataBundle\Entity\Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    public function getItem()
    {
        $result = array();
        $result['status'] = 'current';//$this->getContracts()->count();
        $result['name'] = $this->getFullName();
        $result['email'] = $this->getEmail();
        $result['phone'] = $this->formatPhoneOutput($this->getPhone());

        return $result;
    }

    public function getFomattedPhone()
    {
        return $this->formatPhoneOutput($this->getPhone());
    }
}
