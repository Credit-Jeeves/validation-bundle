<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\DataBundle\Model\Transaction as Base;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\TransactionRepository")
 * @ORM\Table(name="rj_transaction")
 */
class Transaction extends Base
{
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * Date time of actual payment transaction with Heartland
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Date")
     * @Serializer\Groups({"rentTrackReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getActualPaymentTransactionDate()
    {
        return $this->getCreatedAt()->format('Y-m-d\TH:i:s');
    }

    /**
     * @return null|Contract
     */
    public function getContract()
    {
        if ($order = $this->getOrder()) {
            return $order->getContract();
        }

        return null;
    }

    /**
     * @return null|Unit
     */
    public function getUnit()
    {
        if ($contract = $this->getContract()) {
            return $contract->getUnit();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function getIsIntegratedGroup()
    {
        $contract = $this->getContract();
        if ($contract && $group = $contract->getGroup()) {
            return $group->getGroupSettings()->getIsIntegrated();
        }

        return false;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Address")
     * @Serializer\Groups({"rentTrackReport"})
     *
     * @return string
     */
    public function getPropertyAddress()
    {
        $contract = $this->getContract();

        if ($contract && ($property = $contract->getProperty())) {
            return $property->getFullAddress();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Groups({"rentTrackReport"})
     *
     * @return string
     */
    public function getUnitId()
    {
        $unit = $this->getUnit();

        if (!$unit) {
            return null;
        }

        if ($this->getIsIntegratedGroup() && $unitMapping = $unit->getUnitMapping()) {
            return $unitMapping->getExternalUnitId();
        }

        return null;
    }



    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UnitName")
     * @Serializer\Groups({"rentTrackReport"})
     *
     * @return string
     */
    public function getUnitName()
    {
        $unitName = '';

        if ($unit = $this->getUnit()) {
            $unitName = $unit->getName();
        }

        return $unitName;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("ResidentID")
     * @Serializer\Groups({"rentTrackReport"})
     */
    public function getTenantId()
    {
        $contract = $this->getContract();

        if (!$contract || !($tenant = $contract->getTenant())) {
            return null;
        }

        if ($this->getIsIntegratedGroup() && $tenantMapping = $tenant->getResidentsMapping()->first()) {
            return $tenantMapping->getResidentId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("TenantName")
     * @Serializer\Groups({"rentTrackReport"})
     */
    public function getTenantName()
    {
        $contract = $this->getContract();

        if (!$contract || !($tenant = $contract->getTenant())) {
            return null;
        }

        return $tenant->getFullName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Amount")
     * @Serializer\Groups({"rentTrackReport"})
     *
     * @return float
     */
    public function getTotalAmount()
    {
        $order = $this->getOrder();
        $amount = $order ? $order->getSum() : 0;

        if ($this->getStatus() == TransactionStatus::REVERSED) {
            $amount = -$amount;
        }

        return number_format($amount, 2, '.', '');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("TransactionID")
     * @Serializer\Groups({"rentTrackReport"})
     */
    public function getHeartlandTransactionId()
    {
        return $this->getTransactionId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("BatchID")
     * @Serializer\Groups({"rentTrackReport"})
     *
     */
    public function getHeartlandBatchId()
    {
        return $this->getBatchId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Status")
     * @Serializer\Groups({"rentTrackReport"})
     */
    public function getOrderStatus()
    {
        if ($order = $this->getOrder()) {
            $status = $order->getStatus();
            if ($status == OrderStatus::PENDING) {
                $status = 'processing'; // We show 'processing' in the UI, let's be consistent
            }
            return $status;
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PaymentType")
     * @Serializer\Groups({"rentTrackReport"})
     */
    public function getPaymentType()
    {
        if ($order = $this->getOrder()) {
            return $order->getOrderTypes();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("GroupName")
     * @Serializer\Groups({"rentTrackReport"})
     *
     * @return string|null
     */
    public function getGroupName()
    {
        $contract = $this->getContract();
        if ($contract && ($group = $contract->getGroup())) {
            return $group->getName();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("DepositDate")
     * @Serializer\Groups({"rentTrackReport"})
     *
     * @return null|string
     */
    public function getDepositDateReport()
    {
        $date = $this->getDepositDate();

        return $date ? $date->format('Y-m-d'): null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("DepositAccountNum")
     * @Serializer\Groups({"rentTrackReport"})
     *
     * @return null|int
     */
    public function getDepositAccountNumber()
    {
        $contract = $this->getContract();

        if (!$contract || !($group = $contract->getGroup())) {
            return null;
        }

        return $group->getAccountNumber();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Type")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"soapYardiReversed"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getReversal()
    {
        /** @var YardiSettings $yardiSettings */
        $yardiSettings = $this->getContract()->getHolding()->getYardiSettings();
        $order = $this->getOrder();

        return $yardiSettings->getReversalType($order);
    }
}
