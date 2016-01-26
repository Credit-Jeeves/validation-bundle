<?php

namespace RentJeeves\DataBundle\Tests\Traits;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManager;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderFactory;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\CoreBundle\DateTime;

trait TransactionAvailableTrait
{
    /**
     * @param string $accountingSystem
     * @param string $residentId
     * @param string $externalPropertyId
     * @param string $externalLeaseId
     * @param string $externalUnitId
     *
     * @return Transaction
     */
    public function createTransaction(
        $accountingSystem,
        $residentId,
        $externalPropertyId,
        $externalLeaseId = null,
        $externalUnitId = null
    ) {
        /** @var EntityManager $em */
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

        if ($externalUnitId !== null) {
            if (false == $unitMapping = $unit->getUnitMapping()) {
                $unitMapping = new UnitMapping();

                $unitMapping->setUnit($unit);
                $unit->setUnitMapping($unitMapping);

                $em->persist($unitMapping);
            }
            $unitMapping->setExternalUnitId($externalUnitId);
        }

        $holding = $contract->getHolding();
        $holding->setAccountingSystem($accountingSystem);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($holding);
        $propertyMapping->setExternalPropertyId($externalPropertyId);

        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId($residentId);

        $em->persist($unit);
        $em->persist($contract);
        $em->persist($propertyMapping);
        $em->persist($holding);
        $em->persist($residentMapping);
        $em->flush();

        $order = OrderFactory::getOrder($contract->getGroup());
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setPaymentType(OrderPaymentType::CARD);
        $order->setDepositAccount($contract->getGroup()->getDepositAccounts()->first());
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setType(OperationType::OTHER);
        $operation->setGroup($contract->getGroup());
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
        $transaction->setTransactionId(rand(9999, 9999999));
        $order->addTransaction($transaction);

        $em->persist($transaction);
        $em->persist($operation);
        $em->persist($order);

        $em->flush();

        return $transaction;
    }
}
