<?php

namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Order as Base;
use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use CreditJeeves\DataBundle\Enum\OperationType;
use JMS\Serializer\GenericSerializationVisitor;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\Unit;
use JMS\Serializer\Annotation as Serializer;
use DateTime;
use RentJeeves\DataBundle\Enum\TransactionStatus;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OrderRepository")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="order_type", type="OrderAlgorithmType")
 * @ORM\DiscriminatorMap({
 *      "submerchant" = "CreditJeeves\DataBundle\Entity\OrderSubmerchant",
 *      "pay_direct" = "CreditJeeves\DataBundle\Entity\OrderPayDirect"
 * })
 * @Serializer\Discriminator(disabled = true)
 *
 * @ORM\Table(name="cj_order")
 */
class Order extends Base
{
    use \RentJeeves\CoreBundle\Traits\DateCommon;

    /**
     * use for ResMan addPaymentToBatch
     *
     * @var string
     */
    protected $batchId = null;

    /**
     * Use for export on preSerialize event
     *
     * @var null
     */
    protected $buildingId = null;

    public function setBuildingId($buildingId)
    {
        $this->buildingId = $buildingId;
    }

    /**
     * @return DateTime
     */
    public function getPostMonth()
    {
        $paidFor = null;
        /** @var Operation $rentOperation */
        foreach ($this->getRentOperations() as $rentOperation) {
            if (!$paidFor || $paidFor < $rentOperation->getPaidFor()) {
                $paidFor = $rentOperation->getPaidFor();
            }
        }

        return $paidFor ? $paidFor->format('Y-m-d\TH:i:s') : '';
    }

    public function getPropertyAddress()
    {
        $contract = $this->getContract();
        if (!$contract) {
            return null;
        }

        $property = $contract->getProperty();

        if (!$property) {
            return null;
        }

        return $property->getFullAddress();
    }

    /**
     * @return null | Unit
     */
    public function getUnit()
    {
        $contract = $this->getContract();

        if (!$contract) {
            return null;
        }

        $unit = $contract->getUnit();

        return $unit;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("BuildingId")
     * @Serializer\Groups({"realPageReport"})
     */
    public function getBuilding()
    {
        return $this->buildingId;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("R")
     * @Serializer\Groups({"YardiGenesis", "YardiGenesisV2"})
     */
    public function getR()
    {
        return "R";
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Transaction ID")
     * @Serializer\Groups({"YardiGenesis", "YardiGenesisV2"})
     */
    public function getYardiGenesisTransactionId()
    {
        return $this->getHeartlandDepositTransactionId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Resident ID")
     * @Serializer\Groups({"YardiGenesis", "YardiGenesisV2"})
     */
    public function getYardiGenesisResidentId()
    {
        return $this->getTenantExternalId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Amount")
     * @Serializer\Groups({"YardiGenesis", "YardiGenesisV2"})
     */
    public function getYardiGenesisAmount()
    {
        return $this->getTotalAmount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Date")
     * @Serializer\Groups({"YardiGenesis", "YardiGenesisV2"})
     */
    public function getYardiGenesisDate()
    {
        return $this->getCreatedAt()->format('m/d/Y');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Description")
     * @Serializer\Groups({"YardiGenesis", "YardiGenesisV2"})
     */
    public function getYardiGenesisDescription()
    {
        return sprintf(
            '%s %s',
            $this->getContract()->getTenantRentAddress(),
            $this->getHeartlandBatchId()
        );
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("empty1")
     * @Serializer\Groups({"YardiGenesisV2"})
     */
    public function getEmpty1()
    {
        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("empty3")
     * @Serializer\Groups({"YardiGenesisV2"})
     */
    public function getEmpty3()
    {
        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("empty2")
     * @Serializer\Groups({"YardiGenesisV2"})
     */
    public function getEmpty2()
    {
        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Unit Number")
     * @Serializer\Groups({"realPageReport"})
     *
     * @return string
     */
    public function getUnitName()
    {
        $unitName = '';

        $unit = $this->getUnit();

        if ($unit) {
            $unitName = $unit->getName();
        }

        return $unitName;
    }

    /**
     * Date time of actual payment transaction with Heartland
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Date")
     * @Serializer\Groups({"realPageReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return DateTime
     */
    public function getActualPaymentTransactionDate()
    {
        return $this->getCreatedAt()->format('m/d/Y');
    }

    /**
     * Date time of actual payment transaction with Heartland
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Date")
     * @Serializer\Groups({"promasReport"})
     * @Serializer\Type("string")
     *
     * @return DateTime
     */
    public function getPaymentDateForPromas()
    {
        return $this->getCreatedAt()->format('Y-m-d');
    }

    /**
     * External UNIT ID for Promas report
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UNIT ID")
     * @Serializer\Groups({"promasReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getExternalUnitId()
    {
        $unit = $this->getUnit();
        if ($unit && $unitMapping = $unit->getUnitMapping()) {
            return $unitMapping->getExternalUnitId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("TotalAmount")
     * @Serializer\Groups({"realPageReport", "promasReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return number_format($this->getSum(), 2, '.', '');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Memo")
     * @Serializer\Groups({"promasReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getPromasMemo()
    {
        return sprintf('Trans #%s Batch #%s', $this->getHeartlandDepositTransactionId(), $this->getHeartlandBatchId());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Tenant ID")
     * @Serializer\Groups({"promasReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getTenantExternalId()
    {
        $holding = $this->getContract()->getHolding();
        if ($residentMapping = $this->getContract()->getTenant()->getResidentForHolding($holding)) {
            return $residentMapping->getResidentId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("First_Name")
     * @Serializer\Groups({"realPageReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getFirstNameTenant()
    {
        if (!$contract = $this->getContract()) {
            return null;
        }
        $tenant = $contract->getTenant();

        if (!$tenant) {
            return null;
        }

        $firstName = str_replace(",", "", $tenant->getFirstName());

        return $firstName;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Last_Name")
     * @Serializer\Groups({"realPageReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getLastNameTenant()
    {
        if (!$contract = $this->getContract()) {
            return null;
        }
        $tenant = $contract->getTenant();

        if (!$tenant) {
            return null;
        }

        $lastName = str_replace(",", "", $tenant->getLastName());

        return $lastName;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Code")
     * @Serializer\Groups({"realPageReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getCode()
    {
        if ($this->getType() === OrderType::HEARTLAND_CARD) {
            $code = 'PMTCRED';
        } elseif ($this->getType() === OrderType::HEARTLAND_BANK) {
            $code = 'PMTCHECK';
        } elseif ($this->getType() === OrderType::CASH) {
            $code = 'EXTERNAL';
        } else {
            $code = '';
        }

        return $code;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("DocumentNumber")
     * @Serializer\Groups({"realPageReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getRealPageDocumentNumber()
    {
        return $this->getHeartlandDepositTransactionId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Description")
     * @Serializer\Groups({"realPageReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getDescription()
    {
        return sprintf(
            '%s #%s BATCH# %d',
            str_replace(",", "", $this->getPropertyAddress()), // RealPage import doesn't honor quoted strings
            $this->getUnitName(),
            $this->getHeartlandBatchId()
        );
    }

    /**
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRentOperations()
    {
        return $this->getOperations()
            ->filter(
                function (Operation $operation) {
                    if (OperationType::RENT == $operation->getType()) {
                        return true;
                    }

                    return false;
                }
            );
    }

    /**
     * @return Operation|null
     */
    public function getOtherOperation()
    {
        $operationCollection = $this->getOperations()
            ->filter(
                function (Operation $operation) {
                    if (OperationType::OTHER == $operation->getType()) {
                        return true;
                    }

                    return false;
                }
            );

        if (0 == $operationCollection->count()) {
            return null;
        }

        return $operationCollection->last();
    }

    public function getRentAmount()
    {
        $result = 0;
        foreach ($this->getRentOperations() as $operation) {
            $result += $operation->getAmount();
        }

        return number_format($result, 2, '.', '');
    }

    public function getOtherAmount()
    {
        $result = 0;
        if ($this->getOtherOperation()) {
            $result = $this->getOtherOperation()->getAmount();
        }

        return number_format($result, 2, '.', '');
    }

    public function addOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $return = parent::addOperation($operation);
        if (!$operation->getOrder()) {
            $operation->setOrder($this);
        }

        return $return;
    }

    public function setOperations($operations)
    {
        /** @var Operation $operation */
        foreach ($operations as $operation) {
            $this->addOperation($operation);
        }

        return $this;
    }

    /**
     * @Serializer\Groups({"payment"})
     * @Serializer\HandlerCallback("json", direction = "serialization")
     *
     * @return array
     */
    public function getItem(GenericSerializationVisitor $visitor = null)
    {
        $result = array();
        /** @var Contract $contract */
        $contract = $this->getOperations()->last()->getContract();
        $result['amount'] = $this->getSum(); //TODO check. May be it must be operation getAmount()
        $result['tenant'] = $contract ? $contract->getTenant()->getFullName() : '';
        $result['address'] = $contract ? $contract->getRentAddress($contract->getProperty(), $contract->getUnit()) : '';
        $result['start'] = $this->getCreatedAt()->format('m/d/Y');
        $depositDate = $this->getDepositDate();
        $result['depositDate'] = $depositDate ? $depositDate->format('m/d/Y') : 'N/A';
        $result['finish'] = '--';
        $result['style'] = $this->getOrderStatusStyle();
        $result['icon'] = $this->getOrderTypes();
        $status = $this->getStatus();
        $result['status'] = 'order.status.text.'.$status;
        $result['errorMessage'] = $this->getHeartlandMessage();
        switch ($status) {
            case OrderStatus::COMPLETE:
                $result['finish'] = $this->getCreatedAt()->format('m/d/Y');
                break;
            case OrderStatus::PENDING:
                $result['finish'] = $this->getCreatedAt()->format('m/d/Y');
                break;
            case OrderStatus::ERROR:
            case OrderStatus::CANCELLED:
            case OrderStatus::REFUNDED:
            case OrderStatus::RETURNED:
                $result['finish'] = $this->getCreatedAt()->format('m/d/Y');
                break;
        }

        if ($visitor !== null) {
            $visitor->setRoot($result);
        }

        return $result;
    }

    public function getOrderTypes()
    {
        $type = $this->getType();
        switch ($type) {
            case OrderType::HEARTLAND_CARD:
                $result = 'credit-card';
                break;
            case OrderType::HEARTLAND_BANK:
                $result = 'e-check';
                break;
            case OrderType::CASH:
                $result = 'cash';
                break;
            default:
                $result = '';
                break;
        }

        return $result;
    }

    protected function getOrderStatusStyle()
    {
        switch ($this->getStatus()) {
            case OrderStatus::COMPLETE:
                $style = '';
                break;
            case OrderStatus::ERROR:
            case OrderStatus::CANCELLED:
            case OrderStatus::REFUNDED:
            case OrderStatus::RETURNED:
                $style = 'late';
                break;
            default:
                $style = 'contract-pending';
        }

        return $style;
    }

    /**
     * @return array
     */
    public function getTenantPayment()
    {
        $result = array();
        $result['status'] = $this->getStatus();
        $result['errorMessage'] = $this->getHeartlandMessage();
        $result['style'] = $this->getOrderStatusStyle();
        $result['date'] = $this->getCreatedAt()->format('m/d/Y');
        $result['property'] = $this->getContract() ? $this->getContract()->getRentAddress() : 'N/A';

        $result['rent'] = $this->getRentAmount();
        $result['other'] = $this->getOtherAmount();
        $result['total'] = $this->getTotalAmount();
        $result['type'] = $this->getOrderTypes();

        return $result;
    }

    public function getHeartlandTransaction()
    {
        if (OrderStatus::RETURNED == $this->getStatus() ||
            OrderStatus::REFUNDED == $this->getStatus() ||
            OrderStatus::CANCELLED == $this->getStatus()) {
            $transaction = $this->getReversedTransaction();
        } else {
            $transaction = $this->getCompleteTransaction();
        }

        if ($transaction) {
            return $transaction;
        }

        return null;
    }

    public function getHeartlandTransactionId()
    {
        if ($transaction = $this->getHeartlandTransaction()) {
            return $transaction->getTransactionId();
        }

        return null;
    }

    public function getHeartlandDepositTransactionId()
    {
        if ($transaction = $this->getCompleteTransaction()) {
            return $transaction->getTransactionId();
        }

        return null;
    }

    /**
     * @return boolean|Transaction
     */
    public function getCompleteTransaction()
    {
        return $this->getTransactions()
            ->filter(
                function (Transaction $transaction) {
                    if (TransactionStatus::COMPLETE == $transaction->getStatus() && $transaction->getIsSuccessful()) {
                        return true;
                    }

                    return false;
                }
            )->first();
    }

    public function getReversedTransaction()
    {
        return $this->getTransactions()
            ->filter(
                function (Transaction $transaction) {
                    if (TransactionStatus::REVERSED == $transaction->getStatus() && $transaction->getIsSuccessful()) {
                        return true;
                    }

                    return false;
                }
            )->first();
    }

    public function getHeartlandBatchId()
    {
        /** @var Transaction $transaction */
        if ($transaction = $this->getHeartlandTransaction()) {
            return $transaction->getBatchId();
        }

        return null;
    }

    /**
     * @param  bool   $onlyReversal
     * @return string
     */
    public function getHeartlandMessage($onlyReversal = true)
    {
        if (OrderStatus::ERROR == $this->getStatus()) {
            return $this->getErrorMessage();
        }

        if ($onlyReversal) {
            if (in_array($this->getStatus(), [OrderStatus::CANCELLED, OrderStatus::RETURNED, OrderStatus::REFUNDED])) {
                return $this->getReversalDescription();
            }

            return '';
        }

        return $this->getHeartlandTransaction() ? $this->getHeartlandTransaction()->getMessages() : '';
    }

    /**
     * Reversed transactions are added from csv report, so error message may be only for complete transaction type
     *
     * @return string
     */
    public function getErrorMessage()
    {
        /** @var Transaction $transaction */
        $transaction = $this->getTransactions()
            ->filter(
                function (Transaction $transaction) {
                    if (TransactionStatus::COMPLETE == $transaction->getStatus() && !$transaction->getIsSuccessful()) {
                        return true;
                    }

                    return false;
                }
            )->last();

        if ($transaction) {
            return $transaction->getMessages();
        }

        return '';
    }

    public function getReversalDescription()
    {
        /** @var Transaction $transaction */
        if ($transaction = $this->getReversedTransaction()) {
            return $transaction->getMessages();
        }

        return '';
    }

    /**
     * @param bool   $asString Defines whether to return a string or an array
     * @param string $glue     A glue for string result
     *
     * @return array|string
     */
    public function getHeartlandTransactionIds($asString = true, $glue = ', ')
    {
        $result = array();
        /** @var Transaction $transaction */
        foreach ($this->getTransactions() as $transaction) {
            $result[] = $transaction->getTransactionId();
        }

        if ($asString) {
            return implode($glue, $result);
        }

        return $result;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Contract | null
     */
    public function getContract()
    {
        /** @var Operation $operation */
        if ($operation = $this->getOperations()->last()) {
            return $operation->getContract();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasContract()
    {
        if ($this->getContract()) {
            return true;
        }

        return false;
    }

    public function getAvailableOrderStatuses()
    {
        return OrderStatus::getManualAvailableToSet($this->getStatus());
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("DocumentNumber")
     * @Serializer\Groups({"soapYardiRequest"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     */
    public function getDocumentNumber()
    {
        return $this->getHeartlandTransactionId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("DocumentNumber")
     * @Serializer\Groups({"soapYardiReversed", "ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     */
    public function getReversedDocumentNumber()
    {
        if ($transaction = $this->getCompleteTransaction()) {
            return $transaction->getTransactionId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Reversal")
     * @Serializer\Groups({"soapYardiReversed"})
     * @Serializer\Type("RentJeeves\DataBundle\Entity\Transaction")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getReversal()
    {
        return $this->getReversedTransaction();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("TransactionDate")
     * @Serializer\Groups({"soapYardiRequest", "soapYardiReversed", "ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     */
    public function getTransactionDate()
    {
        return $this->getCreatedAt()->format('Y-m-d');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("BatchID")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     */
    public function getResManBatchId()
    {
        return $this->batchId;
    }

    /**
     * @param string $batchId
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * @return string
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UnitID")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return null|string
     */
    public function getResManUnitId()
    {
        $unitMapping = $this->getContract()->getUnit()->getUnitMapping();

        if ($unitMapping) {
            return $unitMapping->getExternalUnitId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Groups({"soapYardiRequest", "soapYardiReversed", "ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     */
    public function getCustomerID()
    {
        return strtolower($this->getContract()->getExternalLeaseId());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PaidBy")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     */
    public function getPaidBy()
    {
        return $this->getContract()->getTenant()->getFullName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Amount")
     * @Serializer\Groups({"soapYardiRequest", "soapYardiReversed", "ResMan"})
     * @Serializer\Type("double")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return double
     */
    public function getYardiAmount()
    {
        return $this->getTotalAmount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Comment")
     * @Serializer\Groups({"soapYardiRequest", "soapYardiReversed", "ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getContract()->getProperty()->getFullAddress();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Description")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getResManDescription()
    {
        return $this->getComment();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PropertyPrimaryID")
     * @Serializer\Groups({"soapYardiRequest", "soapYardiReversed", "ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string|null
     */
    public function getPropertyPrimaryId()
    {
        /** @var PropertyMapping $propertyMapping */
        $propertyMapping = $this->getContract()->getProperty()->getPropertyMappingByHolding(
            $this->getContract()->getGroup()->getHolding()
        );
        if (null === $propertyMapping) {
            return null;
        }

        return strtolower($propertyMapping->getExternalPropertyId());
    }

    public function getDepositDate()
    {
        if ($transaction = $this->getCompleteTransaction()) {
            return $transaction->getDepositDate();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("SiteID")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getMriSiteId()
    {
        return $this->getContract()->getHolding()->getMriSettings()->getSiteId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PaymentType")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string|null
     */
    public function getMriPaymentType()
    {
        if ($this->getType() === OrderType::HEARTLAND_CARD) {
            return 'K';
        }

        if ($this->getType() === OrderType::HEARTLAND_BANK) {
            return 'C';
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PaymentAmount")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getMriPaymentAmount()
    {
        return $this->getTotalAmount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("CheckNumber")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getMriCheckNumber()
    {
        if ($this->getType() === OrderType::HEARTLAND_BANK) {
            return $this->getCompleteTransaction()->getTransactionId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("ExternalTransactionNumber")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getMriExternalTransactionNumber()
    {
        return $this->getCompleteTransaction()->getTransactionId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PaymentInitiationDatetime")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getMriPaymentInitiationDatetime()
    {
        return $this->getCreatedAt()->format('Y-m-d\TH:i:s');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("ResidentNameID")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getMriResidentNameID()
    {
        return $this->getTenantExternalId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PropertyID")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getMriPropertyID()
    {
        return $this->getPropertyPrimaryId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PartnerName")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getPartnerName()
    {
        return 'RentTrack';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("ExternalBatchId")
     * @Serializer\Groups({"MRI"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getExternalBatchId()
    {
        return $this->getCompleteTransaction()->getBatchId();
    }
}
