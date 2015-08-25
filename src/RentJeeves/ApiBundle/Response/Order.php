<?php

namespace RentJeeves\ApiBundle\Response;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\DataBundle\Entity\Order as Entity;

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
        if ($paymentAccount = $this->entity->getPaymentAccount()
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
        if ($paymentAccount = $this->entity->getPaymentAccount()) {
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
        switch ($this->entity->getPaymentType()) {
            case OrderPaymentType::BANK:
                return 'bank';
            case OrderPaymentType::CARD:
                return 'card';
            default:
                return $this->entity->getPaymentType();
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
    public function getRent()
    {
        return $this->entity->getRentAmount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"OrderDetails"})
     * @return string
     */
    public function getOther()
    {
        return $this->entity->getOtherAmount();
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
