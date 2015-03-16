<?php

namespace RentJeeves\DataBundle\Tests\Traits;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\CoreBundle\DateTime;

trait TransactionAvailableTrait
{
    /**
     * @return Heartland
     */
    public function createTransaction()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        /** @var $contract Contract */
        $contract = $this->getContract($startAt, $finishAt);
        $contract->setExternalLeaseId('09948a58-7c50-4089-8942-77e1456f40ec');
        $unit = $contract->getUnit();
        $unit->setName('2');
        $holding = $contract->getHolding();
        $holding->getAccountingSettings()->setApiIntegration(ApiIntegrationType::RESMAN);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($holding);
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);

        $em->persist($unit);
        $em->persist($contract);
        $em->persist($propertyMapping);
        $em->persist($holding);
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

        $transaction = new Heartland();
        $transaction->setAmount(500);
        $transaction->setOrder($order);
        $transaction->setBatchId(55558888);
        $transaction->setBatchDate(new DateTime());
        $transaction->setStatus(TransactionStatus::COMPLETE);
        $transaction->setIsSuccessful(true);
        $transaction->setTransactionId(uniqid());
        $order->addHeartland($transaction);

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
