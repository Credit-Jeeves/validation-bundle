<?php

namespace RentJeeves\DataBundle\Tests\Traits;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\CoreBundle\DateTime;

trait TransactionAvailableTrait
{
    /**
     * @param string $apiIntegrationType
     * @param string $residentId
     * @param string $externalProperyId
     * @param null $externalLeaseId
     * @return Transaction
     */
    public function createTransaction($apiIntegrationType, $residentId, $externalProperyId, $externalLeaseId = null)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        /** @var $contract Contract */
        $contract = $this->getContract($startAt, $finishAt);
        if (!empty($externalLeaseId)) {
            $contract->setExternalLeaseId($externalLeaseId);
        }
        $unit = $contract->getUnit();
        $unit->setName('2');
        $holding = $contract->getHolding();
        $holding->getAccountingSettings()->setApiIntegration($apiIntegrationType);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($holding);
        $propertyMapping->setExternalPropertyId($externalProperyId);

        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId($residentId);

        $em->persist($unit);
        $em->persist($contract);
        $em->persist($propertyMapping);
        $em->persist($holding);
        $em->persist($residentMapping);
        $em->flush();

        $order = new Order();
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setType(OrderType::HEARTLAND_CARD);
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setType(OperationType::OTHER);
        $paidFor = new DateTime();
        $operation->setPaidFor($paidFor);
        $operation->setOrder($order);

        $transaction = new Transaction();
        $transaction->setAmount(500);
        $transaction->setOrder($order);
        $transaction->setBatchId(55558888);
        $transaction->setBatchDate(new DateTime());
        $transaction->setStatus(TransactionStatus::COMPLETE);
        $transaction->setIsSuccessful(true);
        $transaction->setTransactionId(uniqid());
        $order->addTransaction($transaction);

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $em->getRepository('RjDataBundle:PaymentBatchMapping');

        $this->assertFalse($repo->isOpenedBatch(
            $transaction->getBatchId(),
            ApiIntegrationType::RESMAN,
            ResManClientCase::EXTERNAL_PROPERTY_ID
        ));
        $em->persist($transaction);
        $em->persist($operation);
        $em->persist($order);

        $em->flush();

        return $transaction;
    }
}
