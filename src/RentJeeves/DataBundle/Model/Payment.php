<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\PaymentStatus;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Payment
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Tenant",
     *     inversedBy="payments"
     * )
     * @ORM\JoinColumn(
     *     name="tenant_id",
     *     referencedColumnName="id"
     * )
     */
    protected $tenant;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     inversedBy="payments"
     * )
     * @ORM\JoinColumn(
     *     name="contract_id",
     *     referencedColumnName="id"
     * )
     */
    protected $contract;

    /**
     * 
     * @ORM\Column(
     *     type="integer"
     * )
     */
    protected $amount;

    /**
     * @ORM\Column(
     *     type="PaymentStatus",
     *     options={
     *         "default"="pending"
     *     }
     * )
     */
    protected $status;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     */
    protected $updatedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Tenant
     *
     * @param \CreditJeeves\DataBundle\Entity\Tenant $tenant
     * @return contract
     */
    public function setTenant(\CreditJeeves\DataBundle\Entity\Tenant $tenant)
    {
        $this->tenant = $tenant;
        return $this;
    }

    /**
     * Get Tenant
     *
     * @return \CreditJeeves\DataBundle\Entity\Tenant
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Set Contract
     *
     * @param Contract $contract
     * @return Payment
     */
    public function setContract(Contract $contract)
    {
        $this->contract = $contract;
        return $this;
    }

    /**
     * Get Contract
     *
     * @return Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set Amount
     *
     * @param integer $amount
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get Amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Payment
     */
    public function setStatus($status = ContractStatus::PENDING)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Payment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Payment
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
