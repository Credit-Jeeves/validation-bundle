<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use JMS\Serializer\Annotation as Serializer;

class MRIOrder
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
        return $this->order->getContract()->getHolding()->getMriSettings()->getSiteId();
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
        if ($this->order->getPaymentType() === OrderPaymentType::CARD) {
            return 'K';
        }

        if ($this->order->getPaymentType() === OrderPaymentType::BANK) {
            return 'C';
        }

        if ($this->order->getPaymentType() === OrderPaymentType::SCANNED_CHECK) {
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
        return $this->order->getTotalAmount();
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
        if ($this->order->getPaymentType() === OrderPaymentType::BANK) {
            return $this->order->getCompleteTransaction()->getTransactionId();
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
        return $this->order->getCompleteTransaction()->getTransactionId();
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
        return $this->order->getCreatedAt()->format('Y-m-d\TH:i:s');
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
        return $this->order->getTenantExternalId();
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
        return $this->order->getPropertyPrimaryId();
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
        return $this->order->getCompleteTransaction()->getBatchId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Description")
     * @Serializer\Groups({"MRI-with-description"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getMriDescription()
    {
        return sprintf(
            'RT T#%s B#%s',
            $this->order->getCompleteTransaction()->getTransactionId(),
            $this->order->getCompleteTransaction()->getBatchId()
        );
    }

    /**
     * @return boolean
     */
    public function isSendDescription()
    {
        /** @var Operation $operation */
        $operation = $this->order->getOperations()->first();

        if (!$operation->getContract()) {
            return false;
        }

        return $operation->getContract()->getHolding()->getMriSettings()->isSendDescription();
    }
}
