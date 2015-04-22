<?php

namespace RentJeeves\ApiBundle\Response;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use CreditJeeves\DataBundle\Entity\Order as Entity;

/**
 * @DI\Service("response_resource.order")
 * @UrlResourceMeta(
 *      actionName = "get_order"
 * )
 */
class Order extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderShort", "OrderDetails"})
     * @return string
     */
    public function getStatus()
    {
        return $this->entity->getStatus();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getContractUrl()
    {
        return $this
            ->resourceFactory
            ->getResponse($this->entity->getContract());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getPaymentAccountUrl()
    {
        if ($transaction = $this->getTransaction()
            and $paymentAccount = $transaction->getPaymentAccount()
            and !$paymentAccount->getDeletedAt()
        ) {
            return $this
                ->resourceFactory
                ->getResponse($paymentAccount);
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getReferenceId()
    {
        if ($transaction = $this->getTransaction()) {
            return $transaction->getTransactionId();
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getPaymentSource()
    {
        if ($transaction = $this->getTransaction()
            and $paymentAccount = $transaction->getPaymentAccount()
        ) {
            return $paymentAccount->getName();
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getType()
    {
        switch ($this->entity->getType()) {
            case OrderType::HEARTLAND_BANK:
                return 'bank';
            case OrderType::HEARTLAND_CARD:
                return 'card';
            default:
                return $this->entity->getType();
        }
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getMessage()
    {
        if ($transaction = $this->entity->getHeartlandTransaction() or $transaction = $this->getTransaction()) {
            return $transaction->getMessages() ?: '';
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getTotal()
    {
        return $this->entity->getTotalAmount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getPaidFor()
    {
        if ($this->entity->getRentOperations()->count() > 0) {
            /** @var \CreditJeeves\DataBundle\Entity\Operation $operation */
            $operation = $this->entity->getRentOperations()->last();

            if ($operation->getPaidFor()) {
                return $operation->getPaidFor()->format('Y-m');
            }
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->entity->getCreatedAt()->format('U');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getDepositedAt()
    {
        if ($transaction = $this->entity->getCompleteTransaction() and $transaction->getDepositDate()) {
            return $transaction->getDepositDate()->format('Y-m-d');
        }

        return '';
    }

    protected function getTransaction()
    {
        if ($this->entity->getStatus() == OrderStatus::ERROR) {
            return $this->entity->getTransactions()->first();
        } else {
            return $this->entity->getCompleteTransaction();
        }
    }
}
