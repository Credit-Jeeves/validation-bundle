<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\SynchronizationStrategy;
use RentJeeves\DataBundle\Enum\YardiPostMonthOption;

class PaymentDetail
{
    /** @var Order */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("DocumentNumber")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getDocumentNumber()
    {
        if ($transaction = $this->order->getCompleteTransaction()) {
            return $transaction->getTransactionId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Reversal")
     * @Serializer\Groups({"reversedPayment"})
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\Yardi\ReversalType")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getReversal()
    {
        return new ReversalType($this->order);
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("TransactionDate")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getTransactionDate()
    {
        return $this->order->getCreatedAt()->format('Y-m-d');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PostMonth")
     * @Serializer\Groups({"withPostMonth"})
     * @Serializer\Type("double")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return double
     */
    public function getPostMonth()
    {
        // default post month date is created_at date
        $postMonthDate = $this->order->getCreatedAt();
        $postMonthOption = $this->order->getContract()->getHolding()->getYardiSettings()->getPostMonthNode();

        if (YardiPostMonthOption::DEPOSIT_DATE == $postMonthOption) {
            $postMonthDate = $this->calcDepositDate();
        }

        return $postMonthDate->format('Y-m-d');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getCustomerID()
    {
        return strtolower($this->order->getContract()->getExternalLeaseId());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Amount")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     * @Serializer\Type("double")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return double
     */
    public function getAmount()
    {
        return $this->order->getTotalAmount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Comment")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getComment()
    {
        return $this->order->getContract()->getProperty()->getFullAddress();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PropertyPrimaryID")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string|null
     */
    public function getPropertyPrimaryId()
    {
        /** @var PropertyMapping $propertyMapping */
        $propertyMapping = $this->order->getContract()->getProperty()->getPropertyMappingByHolding(
            $this->order->getContract()->getHolding()
        );
        if (null === $propertyMapping) {
            return null;
        }

        return strtolower($propertyMapping->getExternalPropertyId());
    }

    /**
     * @return \DateTime
     */
    protected function calcDepositDate()
    {
        if (OrderPaymentType::BANK === $this->order->getPaymentType() &&
            PaymentProcessor::HEARTLAND == $this->order->getPaymentProcessor()
        ) {
            $synchronizationStrategy = $this->order
                ->getContract()
                ->getHolding()
                ->getYardiSettings()
                ->getSynchronizationStrategy();

            if (SynchronizationStrategy::REAL_TIME === $synchronizationStrategy) {
                // estimate to 3 business days into the future
                return BusinessDaysCalculator::getBusinessDate($this->order->getCreatedAt(), 3);
            }

            if (SynchronizationStrategy::DEPOSITED === $synchronizationStrategy and
                $transaction = $this->order->getCompleteTransaction() and
                $depositDate = $transaction->getDepositDate()
            ) {
                return $depositDate;
            }
        }

        return BusinessDaysCalculator::getNextBusinessDate($this->order->getCreatedAt());
    }
}
