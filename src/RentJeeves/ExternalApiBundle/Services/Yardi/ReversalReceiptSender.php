<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order as Transaction;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Model\Yardi\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum as SoapClient;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;

/**
 * Service yardi.reversal_receipts
 */
class ReversalReceiptSender
{
    const LIMIT_TRANSACTIONS = 50;

    const LIMIT_HOLDINGS = 50;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SoapClientFactory
     */
    protected $clientFactory;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManager $em
     * @param SoapClientFactory $clientFactory
     * @param Serializer $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $em,
        SoapClientFactory $clientFactory,
        Serializer $serializer,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param \DateTime $depositDate
     * @return boolean
     */
    public function collectReversedPaymentsToJobsByDate(\DateTime $depositDate)
    {
        $this->logger->info('Collect Yardi reversal payments for date:' . $depositDate->format('Y-m-d'));
        try {
            $offsetHoldings = 0;
            while ($holdings = $this->getHoldings($offsetHoldings, self::LIMIT_HOLDINGS)) {
                $this->collectJobsByHoldings($holdings, $depositDate);
                $offsetHoldings += self::LIMIT_HOLDINGS;
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    'Collecting reversals to Yardi failed to complete for all holdings. Exception: %s',
                    $e->getMessage()
                )
            );

            return false;
        }
    }

    /**
     * @param array $holdings
     * @param \DateTime $depositDate
     */
    protected function collectJobsByHoldings(array $holdings, \DateTime $depositDate)
    {
        /** @var Holding $holding */
        foreach ($holdings as $holding) {
            try {
                $this->logger->info(
                    sprintf(
                        'Collecting Yardi reversed jobs for holding: %s',
                        $holding->getName()
                    )
                );
                $offset = 0;

                while ($reversedOrders = $this->getReversedTransactions(
                    $holding,
                    $depositDate,
                    $offset,
                    self::LIMIT_TRANSACTIONS
                )) {
                    $this->createJobs($reversedOrders);
                    $offset += self::LIMIT_TRANSACTIONS;
                }
            } catch (\Exception $e) {
                $this->logger->alert(
                    sprintf(
                        'Reversals for holding(ID:%s) failed collect reversal payments. Exception: %s',
                        $holding->getId(),
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @param array $orders
     */
    protected function createJobs($orders)
    {
        foreach ($orders as $order) {
            $job = new Job(
                'renttrack:yardi:push-reversal-receipt',
                [
                    '--app=rj',
                    sprintf('--order-id=%s', $order->getId()),
                ]
            );
            $this->em->persist($job);
        }
        $this->em->flush();
    }

    /**
     * @param integer $offset
     * @param integer $limit
     * @return array
     */
    protected function getHoldings($offset, $limit)
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsWithYardiSettings($offset, $limit);
    }

    /**
     * @param Holding $holding
     * @param \DateTime $depositDate
     * @param integer $offset
     * @param integer $limit
     * @return array
     */
    protected function getReversedTransactions(Holding $holding, \DateTime $depositDate, $offset, $limit)
    {
        return $this->em->getRepository('DataBundle:Order')->getReversedOrders(
            $holding,
            $depositDate,
            $offset,
            $limit
        );
    }

    /**
     * @param integer $orderId
     * @return boolean
     */
    public function pushReversedReceiptByOrderId($orderId)
    {
        try {
            $order = $this->em->getRepository('DataBundle:Order')->find($orderId);
            if (empty($order)) {
                throw new \LogicException(sprintf('We can\'t find order by ID %s', $orderId));
            }

            return $this->pushReversedOrder($order);
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    'Reversed Order(ID:%s) failed to post to Yardi. Exception: %s',
                    $orderId,
                    $e->getMessage()
                )
            );

            return false;
        }
    }

    /**
     * @param Transaction $order
     * @return bool
     * @throws \Exception
     */
    protected function pushReversedOrder(Transaction $order)
    {
        /** @var Operation $operation */
        $operation = $order->getOperations()->first();
        $settings = $operation->getContract()->getHolding()->getYardiSettings();
        /** @var $residentClient ResidentTransactionsClient */
        $residentClient = $this->clientFactory->getClient($settings, SoapClient::YARDI_RESIDENT_TRANSACTIONS);
        $this->logger->info(
            sprintf(
                'Push Reversed Order to Yardi: %s Original trans: %s',
                $order->getId(),
                $order->getCompleteTransaction()->getTransactionId()
            )
        );
        $transactionXml = $this->getTransactionXml($settings, $order);
        if ($transactionXml === false) {
            $this->logger->alert(sprintf(
                'Order(ID:%s) can not be sent to Yardi, because contract(ID:%s) does not have externalLeaseId.
                You can re-run initial import for setup externalLeaseId for active contract.',
                $order->getId(),
                $order->getContract()->getId()
            ));

            return false;
        }

        /** @var Messages $result */
        $result = $residentClient->importResidentTransactionsLogin($transactionXml);
        if ($result instanceof Messages) {
            $this->logger->info(
                sprintf(
                    'Reversed order ID %s successfully posted to Yardi. Message: %s',
                    $order->getId(),
                    $result->getMessage()->getMessage()
                )
            );

            return true;
        }

        $this->logger->alert(
            sprintf(
                'Reversed Order(ID:%s) failed to post to Yardi: Error: %s',
                $order->getId(),
                $residentClient->getErrorMessage()
            )
        );

        return false;
    }

    /**
     * @param YardiSettings $settings
     * @param Transaction $transaction
     * @return boolean|string
     */
    protected function getTransactionXml(YardiSettings $settings, Transaction $transaction)
    {
        $externalLeaseId = $transaction->getContract()->getExternalLeaseId();

        if (empty($externalLeaseId)) {
            return false;
        }

        $residentTransactions = new ResidentTransactions($settings, [$transaction]);

        $transactionXml = $this->serializer->serialize(
            $residentTransactions,
            'xml',
            SerializationContext::create()->setSerializeNull(true)->setGroups('reversedPayment')
        );
        $transactionXml = YardiXmlCleaner::prepareXml($transactionXml);

        return $transactionXml;
    }
}
