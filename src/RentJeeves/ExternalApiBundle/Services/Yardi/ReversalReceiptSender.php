<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Order as Transaction;
use CreditJeeves\DataBundle\Entity\Order;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Model\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\YardiClientEnum as SoapClient;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;
use Symfony\Component\Console\Output\OutputInterface;

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

    protected $logger;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "clientFactory" = @DI\Inject("soap.client.factory"),
     *     "exceptionCatcher" = @DI\Inject("fp_badaboom.exception_catcher"),
     *     "serializer" = @DI\Inject("jms_serializer")
     * })
     */
    public function __construct(
        EntityManager $em,
        SoapClientFactory $clientFactory,
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer
    ) {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->serializer = $serializer;
    }

    public function usingOutput(OutputInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    protected function logMessage($message)
    {
        if ($this->logger) {
            $this->logger->writeln($message);
        }
    }

    public function run(DateTime $depositDate)
    {
        $this->logMessage('Reversal payments for date:' . $depositDate->format('Y-m-d'));
        try {
            $offsetHoldings = 0;
            while ($holdings = $this->getHoldings($offsetHoldings, self::LIMIT_HOLDINGS)) {

                foreach ($holdings as $holding) {
                    $this->logMessage('Holding: ' . $holding->getName());
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
            $this->logMessage($e->getMessage());
        }
    }

    protected function getHoldings($offset, $limit)
    {
        return $this->em->getRepository('DataBundle:Holding')->findHoldingsWithYardiSettings($offset, $limit);
    }

    protected function getReversedTransactions($holding, $depositDate, $offset, $limit)
    {
        return $this->em->getRepository('DataBundle:Order')->getReversedOrders(
            $holding,
            $depositDate,
            $offset,
            $limit
        );
    }

    protected function pushReceipts(YardiSettings $settings, $transactions)
    {
        /** @var $residentClient ResidentTransactionsClient */
        $residentClient = $this->clientFactory->getClient($settings, SoapClient::RESIDENT_TRANSACTIONS);

        /** @var Order $transaction */
        foreach ($transactions as $transaction) {
            $this->logMessage('Original trans# ' . $transaction->getCompleteTransaction()->getTransactionId());
            $transactionXml = $this->getTransactionXml($settings, $transaction);
            /** @var Messages $result */
            $result = $residentClient->importResidentTransactionsLogin($transactionXml);
            if ($result instanceof Messages) {
                $this->logMessage($result->getMessage());
            } else {
                $this->logMessage(sprintf('Failed to reverse payment: %s', $residentClient->getErrorMessage()));
            }
        }
    }

    protected function getTransactionXml(YardiSettings $settings, Transaction $transaction)
    {
        $residentTransactions = new ResidentTransactions($settings, [$transaction]);

        $transactionXml = $this->serializer->serialize(
            $residentTransactions,
            'xml',
            SerializationContext::create()->setSerializeNull(true)->setGroups('soapYardiReversed')
        );
        $transactionXml = YardiXmlCleaner::prepareXml($transactionXml);

        return $transactionXml;
    }
}
