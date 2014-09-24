<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\SerializationContext;
use \DateTime;
use RentJeeves\ExternalApiBundle\Services\Yardi\YardiBatchReceiptMailer;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\OrderExternalApi;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\ExternalApi;
use RentJeeves\ExternalApiBundle\Model\Payment;
use RentJeeves\ExternalApiBundle\Model\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\PaymentClient;
use RentJeeves\ExternalApiBundle\Soap\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;
use JMS\Serializer\Serializer;
use \Exception;
use Symfony\Component\Console\Output\OutputInterface;
use DOMDocument;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("yardi.push_batch_receipts")
 */
class ReceiptBatchSender
{
    const LIMIT_ORDERS = 500;

    const LIMIT_HOLDING = 50;

    const REQUEST_SUCCESSFUL = 'successfully';

    const REQUEST_FAILED = 'failed';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PaymentClient
     */
    protected $paymentClient;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var SoapClientFactory
     */
    protected $clientFactory;

    /**
     * @var OutputInterface
     */
    protected $logger;

    /**
     * @var DateTime
     */
    protected $depositDate;

    /**
     * @var array
     */
    protected $batchIds = array();

    /**
     * @var array
     */
    protected $requests = array();

    /**
     * @var YardiBatchReceiptMailer
     */
    protected $mailer;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var bool
     */
    protected $isCleanDBAlreadySentOut = true;

    /**
     * @InjectParams({
     *     "em"                 = @Inject("doctrine.orm.default_entity_manager"),
     *     "clientFactory"      = @Inject("soap.client.factory"),
     *     "serializer"         = @Inject("jms_serializer"),
     *     "mailer"             = @Inject("yardi.receipt_mailer"),
     *     "exceptionCatcher"   = @Inject("fp_badaboom.exception_catcher")
     * })
     */
    public function __construct(
        EntityManager $em,
        SoapClientFactory $clientFactory,
        Serializer $serializer,
        YardiBatchReceiptMailer $mailer,
        ExceptionCatcher $exceptionCatcher
    ) {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->mailer = $mailer;
    }

    /**
     * @param OutputInterface $logger
     * @return $this
     */
    public function usingOutput(OutputInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param $isCleanDBAlreadySentOut
     * @return $this
     */
    public function isCleanDBAlreadySentOut($isCleanDBAlreadySentOut)
    {
        $this->isCleanDBAlreadySentOut = $isCleanDBAlreadySentOut;
        return $this;
    }

    /**
     * @param DateTime $depositDate
     */
    public function run(DateTime $depositDate = null)
    {
        if (!$depositDate) {
            $depositDate = new DateTime();
        }

        $this->depositDate = $depositDate;
        $startPagination = 0;
        $this->logMessage("Deposit date: ".$depositDate->format('Y-m-d'));

        if ($this->isCleanDBAlreadySentOut) {
            $this->clearSentOrders();
        }

        try {
            while ($holdings = $this->getHoldingRepository()
                ->findHoldingsWhithYardiSettings($startPagination, self::LIMIT_HOLDING)
            ) {
                $this->pushHoldingReceipts($holdings);
                $startPagination += self::LIMIT_HOLDING;
                $this->em->clear();
                gc_collect_cycles();
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logMessage(sprintf("Failed \n%s", $e->getMessage()));
        }
    }

    protected function clearSentOrders()
    {
        $this->logMessage("Clear OrderExternalApi table");
        $repository = $this->em->getRepository('RjDataBundle:OrderExternalApi');
        $repository->removeByDateAndApiType(
            $this->depositDate,
            ExternalApi::YARDI
        );
    }

    /**
     * @param $holdings
     */
    protected function pushHoldingReceipts($holdings)
    {
        foreach ($holdings as $holding) {
            $this->logMessage(
                sprintf(
                    "Start processing holding with ID:%s and Name:%s",
                    $holding->getId(),
                    $holding->getName()
                )
            );
            $yardiSettings = $holding->getYardiSettings();
            $this->initClient($yardiSettings);
            $this->pushReceiptBatchByBatchId($holding);
            $this->logMessage(
                sprintf(
                    "Sending emails for holding %s",
                    $holding->getId()
                )
            );
            $this->mailer->send($this->requests);
        }
    }

    /**
     * @param Holding $holding
     */
    public function pushReceiptBatchByBatchId(Holding $holding)
    {
        $startPagination = 0;
        while ($orders = $this->getOrderRepository()->getBatchIds(
            $this->depositDate,
            $holding,
            $startPagination,
            self::LIMIT_ORDERS
        )
        ) {
            /**
             * @var $order Order
             */
            foreach ($orders as $order) {
                $batchId =$order->getHeartlandTransaction()->getBatchId();
                $this->pushReceiptBatchByPropertyMapping($holding, $batchId);
            }

            $startPagination += self::LIMIT_ORDERS;
        }
    }

    /**
     * @param Holding $holding
     * @param $batchId
     */
    protected function pushReceiptBatchByPropertyMapping(Holding $holding, $batchId)
    {
        $startPagination = 0;
        while ($orders = $this->getOrderRepository()->getPropertyMapping(
            $this->depositDate,
            $holding,
            $batchId,
            $startPagination,
            self::LIMIT_ORDERS
        )
        ) {
            /**
             * @var $order Order
             */
            foreach ($orders as $order) {
                $property = $order->getContract()->getProperty();
                $mapping = $property->getPropertyMapping()->first();
                $remotePropertyId = $mapping->getLandlordPropertyId();

                try {
                    $this->pushReceiptBatch($holding, $batchId, $remotePropertyId);
                } catch (Exception $e) {
                    $this->logMessage($e->getMessage());
                    $this->exceptionCatcher->handleException($e);
                    continue;
                }
            }
            $startPagination += self::LIMIT_ORDERS;
        }
    }

    /**
     * @param Holding $holding
     * @param $batchId
     * @param $remotePropertyId
     * @throws \Exception
     */
    protected function pushReceiptBatch(Holding $holding, $batchId, $remotePropertyId)
    {
        $startPagination = 0;
        while ($ordersReceiptBatch = $this->getOrderRepository()->getReceiptBatch(
            $this->depositDate,
            $holding,
            $batchId,
            $remotePropertyId,
            $startPagination,
            self::LIMIT_ORDERS
        )
        ) {
            try {
                $yardiBatchId = $this->getBatchId($remotePropertyId, $batchId);
                $result = $this->sendReceiptsBatchToApi(
                    $ordersReceiptBatch,
                    $yardiBatchId
                );

                if ($result === true) {
                    $this->saveSuccessfullRequest($holding, $ordersReceiptBatch, $yardiBatchId);
                } else {
                    $this->saveFailedRequest($holding, $ordersReceiptBatch, $yardiBatchId);
                }
            } catch (Exception $e) {
                if (empty($yardiBatchId) || !isset($yardiBatchId)) {
                    $yardiBatchId = 'undefined';
                }
                $this->saveFailedRequest($holding, $ordersReceiptBatch, $yardiBatchId);
                throw $e;
            }

            $startPagination += self::LIMIT_ORDERS;
        }
    }

    /**
     * @param array $orders
     * @param string $batchId
     */
    protected function sendReceiptsBatchToApi($orders, $batchId)
    {
        $this->logMessage(
            sprintf(
                "Try to send payments in the amount of %s pieces",
                count($orders)
            )
        );
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('soapYardiRequest');
        $residentTransactions = new ResidentTransactions(
            $this->paymentClient->getSettings(),
            $orders
        );
        $xml = $this->serializer->serialize(
            $residentTransactions,
            'xml',
            $context
        );
        $xml = $this->prepareXml($xml);
        $this->paymentClient->addReceiptsToBatch(
            $batchId,
            $xml
        );

        if ($this->paymentClient->isError()) {
            $this->throwExceptionClient(
                sprintf(
                    "Failed add to batchId: %s.",
                    $batchId
                )
            );
        }

        return true;
    }

    /**
     * @param $remotePropertyId
     * @param $batchId
     * @return mixed
     */
    protected function getBatchId($remotePropertyId, $batchId)
    {
        $key = $remotePropertyId.'_'.$batchId;
        if (isset($this->batchIds[$key])) {
            return $this->batchIds[$key];
        }

        $yardiBatchId = $this->paymentClient->openReceiptBatchDepositDate(
            $this->depositDate,
            $remotePropertyId,
            $batchId
        );

        if ($this->paymentClient->isError()) {
            $this->throwExceptionClient(
                sprintf(
                    "Failed create batch for remote property Id: %s.",
                    $remotePropertyId
                )
            );
        }
        $this->logMessage(
            sprintf(
                "Create batchId %s for remote property id %s",
                $yardiBatchId,
                $remotePropertyId
            )
        );
        $this->batchIds[$key] = $yardiBatchId;
        return $yardiBatchId;
    }

    protected function throwExceptionClient($message)
    {
        $response = $this->paymentClient->getFullResponse($isShow = false);
        $request = $this->paymentClient->getFullRequest($isShow = false);
        $this->logMessage($this->paymentClient->getErrorMessage());

        throw new Exception(
            sprintf(
                $message."\nRequest:\n %s %s \n Response:\n %s %s",
                $request['header'],
                $request['body'],
                $response['header'],
                $response['body']
            )
        );
    }

    /**
     * @param string $message
     */
    protected function logMessage($message)
    {
        if ($this->logger) {
            $this->logger->writeln($message);
        }
    }

    /**
     * @param YardiSettings $yardiSettings
     */
    protected function initClient(YardiSettings $yardiSettings)
    {
        $this->paymentClient = $this->clientFactory->getClient(
            $yardiSettings,
            SoapClientEnum::PAYMENT
        );
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getHoldingRepository()
    {
        return $this->em->getRepository('DataBundle:Holding');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getOrderRepository()
    {
        return $this->em->getRepository('DataBundle:Order');
    }

    /**
     * @param $xml
     * @return string
     */
    protected function prepareXml($xml)
    {
        if (empty($xml)) {
            return $xml;
        }

        $domXml = new DOMDocument();
        $domXml->loadXML($xml);
        $xmlOut = $domXml->saveXML($domXml->documentElement);
        $xmlOut = '<ns1:TransactionXml>'.$xmlOut;
        $xmlOut = $xmlOut.'</ns1:TransactionXml>';
        $xmlOut = str_replace(' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xmlOut);

        return $xmlOut;
    }

    /**
     * @param Holding $holding
     * @param $batchId
     */
    protected function fillRequestDefaultData(Holding $holding, $batchId)
    {
        if (!isset($this->requests[$holding->getId()])) {
            $batchId = (empty($batchId))? 'undefined' : $batchId;
            $this->requests[$holding->getId()] = array();
            $settings = $this->paymentClient->getSettings();
            $groups = $holding->getGroups();
            /**
             * @var $group Group
             */
            foreach ($groups as $group) {
                $this->requests[$holding->getId()][$group->getId()] = array(
                    $batchId => array()
                );
                $this->requests[$holding->getId()]
                    [$group->getId()]
                    [$batchId]
                    [Payment::formatType($settings->getPaymentTypeACH())] = array(
                        self::REQUEST_FAILED => 0,
                        self::REQUEST_SUCCESSFUL => 0,
                    );
                $this->requests[$holding->getId()]
                    [$group->getId()]
                    [$batchId]
                    [Payment::formatType($settings->getPaymentTypeCC())] = array(
                        self::REQUEST_FAILED => 0,
                        self::REQUEST_SUCCESSFUL => 0,
                    );
            }
        }
    }

    /**
     * @param Holding $holding
     * @param $orders
     * @param $batchId
     */
    protected function saveSuccessfullRequest(Holding $holding, $orders, $batchId)
    {
        $this->logMessage(
            sprintf(
                "Successfully Request holding: %s batch: %s",
                $holding->getId(),
                $batchId
            )
        );
        $this->fillRequestDefaultData($holding, $batchId);
        /**
         * @var $order Order
         */
        foreach ($orders as $order) {
            $orderExternalApi = new OrderExternalApi();
            $orderExternalApi->setApiType(ExternalApi::YARDI);
            $orderExternalApi->setOrder($order);
            $orderExternalApi->setDepositDate($this->depositDate);

            $this->em->persist($orderExternalApi);

            $typePayment = Payment::getType(
                $this->paymentClient->getSettings(),
                $order->getType()
            );

            $group = $order->getContract()->getGroup();
            $this->requests[$holding->getId()][$group->getId()][$batchId][$typePayment][self::REQUEST_SUCCESSFUL]++;
        }

        $this->em->flush();
    }

    /**
     * @param Holding $holding
     * @param $orders
     * @param $batchId
     */
    protected function saveFailedRequest(Holding $holding, $orders, $batchId)
    {
        $this->logMessage(
            sprintf(
                "Failed Request holding: %s batch: %s",
                $holding->getId(),
                $batchId
            )
        );
        $this->fillRequestDefaultData($holding, $batchId);
        /**
         * @var $order Order
         */
        foreach ($orders as $order) {
            $typePayment = Payment::getType(
                $this->paymentClient->getSettings(),
                $order->getType()
            );
            $group = $order->getContract()->getGroup();
            $this->requests[$holding->getId()][$group->getId()][$batchId][$typePayment][self::REQUEST_FAILED]++;
        }
    }
}
