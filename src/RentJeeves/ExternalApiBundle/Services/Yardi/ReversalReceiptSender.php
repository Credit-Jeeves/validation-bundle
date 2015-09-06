<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Order as Transaction;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Model\Yardi\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum as SoapClient;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;

/**
 * @DI\Service("yardi.push_reversal_receipts")
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
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "clientFactory" = @DI\Inject("soap.client.factory"),
     *     "exceptionCatcher" = @DI\Inject("fp_badaboom.exception_catcher"),
     *     "serializer" = @DI\Inject("jms_serializer"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(
        EntityManager $em,
        SoapClientFactory $clientFactory,
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param DateTime $depositDate
     */
    public function run(DateTime $depositDate)
    {
        $this->logger->info('Reversal payments for date:' . $depositDate->format('Y-m-d'));
        try {
            $offsetHoldings = 0;
            while ($holdings = $this->getHoldings($offsetHoldings, self::LIMIT_HOLDINGS)) {

                foreach ($holdings as $holding) {
                    $this->logger->info('Holding: ' . $holding->getName());
                    $offset = 0;

                    while ($reversedTransactions = $this->getReversedTransactions(
                        $holding,
                        $depositDate,
                        $offset,
                        self::LIMIT_TRANSACTIONS
                    )) {
                        $this->pushReceipts($holding->getYardiSettings(), $reversedTransactions);

                        $offset += self::LIMIT_TRANSACTIONS;
                        $this->em->clear();
                    }
                }
                $offsetHoldings += self::LIMIT_HOLDINGS;
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logger->alert($e->getMessage());
        }
    }

    protected function getHoldings($offset, $limit)
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsWithYardiSettings($offset, $limit);
    }

    /**
     * @param $holding
     * @param $depositDate
     * @param $offset
     * @param $limit
     * @return mixed
     */
    protected function getReversedTransactions($holding, $depositDate, $offset, $limit)
    {
        return $this->em->getRepository('DataBundle:Order')->getReversedOrders(
            $holding,
            $depositDate,
            $offset,
            $limit
        );
    }

    /**
     * @param YardiSettings $settings
     * @param $transactions
     * @throws Exception
     */
    protected function pushReceipts(YardiSettings $settings, $transactions)
    {
        /** @var $residentClient ResidentTransactionsClient */
        $residentClient = $this->clientFactory->getClient($settings, SoapClient::YARDI_RESIDENT_TRANSACTIONS);

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $this->logger->info('Original trans# ' . $transaction->getCompleteTransaction()->getTransactionId());
            $transactionXml = $this->getTransactionXml($settings, $transaction);
            if ($transactionXml === false) {
                $this->logger->alert(sprintf(
                    'Order(ID:%s) can not be sent to Yardi, because contract(ID:%s) does not have externalLeaseId.\n
                    You can re-run initial import for setup externalLeaseId for active contract.',
                    $transaction->getId(),
                    $transaction->getContract()->getId()
                ));
                continue;
            }
            /** @var Messages $result */
            $result = $residentClient->importResidentTransactionsLogin($transactionXml);
            if ($result instanceof Messages) {
                $this->logger->info($result->getMessage());
            } else {
                $this->logger->alert(sprintf('Failed to reverse payment: %s', $residentClient->getErrorMessage()));
            }
        }
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
