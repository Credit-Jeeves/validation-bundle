<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\HoldingRepository;
use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\SerializationContext;
use \DateTime;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\DataBundle\Entity\OrderExternalApi;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\ExternalApi;
use RentJeeves\ExternalApiBundle\Model\Payment;
use RentJeeves\ExternalApiBundle\Model\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\PaymentClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;
use JMS\Serializer\Serializer;
use \Exception;
use Symfony\Component\Console\Output\OutputInterface;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use Psr\Log\LoggerInterface;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("yardi.push_batch_receipts")
 */
class ReceiptBatchSender
{
    const LIMIT_ORDERS = 500;
    const LIMIT_HOLDING = 50;
    const REQUEST_SUCCESSFUL = 'Success';
    const REQUEST_FAILED = 'Failed';

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
    protected $consoleLogger;

    /**
     * @var LoggerInterface
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
     * @var bool
     */
    protected $debug = false;

    /**
     * @InjectParams({
     *     "em"                 = @Inject("doctrine.orm.default_entity_manager"),
     *     "clientFactory"      = @Inject("soap.client.factory"),
     *     "serializer"         = @Inject("jms_serializer"),
     *     "mailer"             = @Inject("yardi.receipt_mailer"),
     *     "exceptionCatcher"   = @Inject("fp_badaboom.exception_catcher"),
     *     "logger"             = @Inject("logger"),
     * })
     */
    public function __construct(
        EntityManager $em,
        SoapClientFactory $clientFactory,
        Serializer $serializer,
        YardiBatchReceiptMailer $mailer,
        ExceptionCatcher $exceptionCatcher,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @param  OutputInterface $logger
     * @return $this
     */
    public function usingOutput(OutputInterface $logger)
    {
        $this->consoleLogger = $logger;

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
                ->findHoldingsWithYardiSettings($startPagination, self::LIMIT_HOLDING)
            ) {
                $this->pushHoldingReceipts($holdings, $depositDate);
                $startPagination += self::LIMIT_HOLDING;
                $this->em->clear();
                gc_collect_cycles();
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logMessage(sprintf("Failed push receipts: \n%s", $e->getMessage()));
        }
    }

    protected function cancelBatch($yardiBatchId)
    {
        try {
            $this->paymentClient->cancelReceiptBatch($yardiBatchId);
            $this->logMessage(sprintf("Cancel batch \n%s", $yardiBatchId));

            if ($this->paymentClient->isError()) {
                throw new Exception(sprintf("Can't cancel batch with id: %s", $yardiBatchId));
            }

            $key = array_search($yardiBatchId, $this->batchIds);
            if (!empty($key)) {
                unset($this->batchIds[$yardiBatchId]);
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logMessage(sprintf("Failed cancel: \n%s", $e->getMessage()));
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
    protected function pushHoldingReceipts($holdings, DateTime $depositDate)
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
            $this->mailer->send($this->requests, $depositDate);
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
                $this->pushReceiptBatch($holding, $batchId);
            }

            $startPagination += self::LIMIT_ORDERS;
        }
    }

    /**
     * @param  Holding    $holding
     * @param $batchId
     * @param $remotePropertyId
     * @throws \Exception
     */
    protected function pushReceiptBatch(Holding $holding, $batchId)
    {
        $startPagination = 0;
        while ($ordersReceiptBatch = $this->getOrderRepository()->getReceiptBatch(
            $this->depositDate,
            $holding,
            $batchId,
            $startPagination,
            self::LIMIT_ORDERS
        )
        ) {
            try {
                $this->removeOrderWhichDoNotHaveLeaseId($ordersReceiptBatch);
                if (empty($ordersReceiptBatch)) {
                    throw new Exception("Nothing to send.");
                }

                if (!isset($remotePropertyId)) {
                    /** @var $order Order */
                    $order = $ordersReceiptBatch[0];
                    $propertyMapping = $order->getContract()->getProperty()->getPropertyMapping();
                    $remotePropertyId = $propertyMapping->first()->getExternalPropertyId();
                }
                $yardiBatchId = $this->getBatchId($remotePropertyId, $batchId);
                $result = $this->sendReceiptsBatchToApi(
                    $ordersReceiptBatch,
                    $yardiBatchId
                );

                if ($result === true) {
                    $this->saveSuccessfullRequest($holding, $ordersReceiptBatch, $yardiBatchId, $batchId);
                } else {
                    $this->saveFailedRequest($holding, $ordersReceiptBatch, $yardiBatchId, $batchId);
                }
                $this->paymentClient->closeReceiptBatch($yardiBatchId);
            } catch (Exception $e) {
                if (empty($yardiBatchId) || !isset($yardiBatchId)) {
                    $yardiBatchId = 'undefined';
                } else {
                    $this->cancelBatch($yardiBatchId);
                }
                $this->saveFailedRequest($holding, $ordersReceiptBatch, $yardiBatchId, $batchId);
                throw $e;
            }

            $startPagination += self::LIMIT_ORDERS;
        }
    }

    /**
     * @param $ordersReceiptBatch
     */
    protected function removeOrderWhichDoNotHaveLeaseId(&$ordersReceiptBatch)
    {
        /** @var Order $order */
        foreach ($ordersReceiptBatch as $key => $order) {
            $leaseId = $order->getContract()->getExternalLeaseId();
            if (!empty($leaseId)) {
                continue;
            }

            unset($ordersReceiptBatch[$key]);
            $message = sprintf(
                "Order(ID:%s) will not send to Yardi, because his contract(ID:%s) does not have externalLeaseId.\n
                 You can re-run initial import for setup externalLeaseId for active contract.
                ",
                $order->getId(),
                $order->getContract()->getId()
            );
            $this->logMessage($message);
        }
    }

    /**
     * @param array  $orders
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
        $xml = YardiXmlCleaner::prepareXml($xml);
        $result = $this->paymentClient->addReceiptsToBatch(
            $batchId,
            $xml
        );

        $this->logMessage(
            sprintf(
                "Add receipts to batchId(%s), result: %s",
                $batchId,
                print_r($result, true)
            )
        );

        if ($this->paymentClient->isError()) {
            $this->logMessage(
                sprintf(
                    "Failed add to batchId: %s.",
                    $batchId
                )
            );
            $this->logger->alert(
                sprintf(
                    'Failed add receipts to batchId(%s), result: %s',
                    $batchId,
                    $this->paymentClient->getErrorMessage()
                )
            );
            $this->logMessage($this->paymentClient->getErrorMessage());

            return false;
        }

        if ($result instanceof Messages && $result->getMessage()->getMessageType() === 'FYI') {
            return true;
        }

        return false;
    }

    /**
     * @param $remotePropertyId
     * @param $batchId
     * @return mixed
     */
    protected function getBatchId($remotePropertyId, $batchId)
    {
        $key = $batchId;
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
                'Create batchId %s for remote property id %s',
                $yardiBatchId,
                $remotePropertyId
            )
        );
        $this->batchIds[$key] = $yardiBatchId;

        return $yardiBatchId;
    }

    /**
     * @param string $message
     * @throws Exception
     */
    protected function throwExceptionClient($message)
    {
        $response = $this->paymentClient->getFullResponse($isShow = false);
        $request = $this->paymentClient->getFullRequest($isShow = false);
        $this->logMessage($this->paymentClient->getErrorMessage());
        $message = sprintf(
            "%s\nRequest:\n %s %s \n Response:\n %s %s",
            $message,
            $request['header'],
            $request['body'],
            $response['header'],
            $response['body']
        );
        $this->logger->critical($message);

        throw new Exception($message);
    }

    /**
     * @param string $message
     */
    protected function logMessage($message)
    {
        if ($this->consoleLogger) {
            $this->consoleLogger->writeln($message);
        }
    }

    /**
     * @param YardiSettings $yardiSettings
     */
    protected function initClient(YardiSettings $yardiSettings)
    {
        $this->paymentClient = $this->clientFactory->getClient(
            $yardiSettings,
            SoapClientEnum::YARDI_PAYMENT
        );
        $this->paymentClient->setDebug($this->debug);
    }

    /**
     * @return HoldingRepository
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

    protected function getKeyForRequest(Holding $holding, Group $group, $batchId)
    {
        return $holding->getId().'_'.$group->getId().'_'.$batchId;
    }

    /**
     * @param Holding $holding
     * @param $yardiBatchId
     * @param $batchId
     */
    protected function fillRequestDefaultData(Holding $holding, $yardiBatchId, $batchId)
    {
        $groups = $holding->getGroups();
        $settings = $this->paymentClient->getSettings();
        foreach ($groups as $group) {

            $key = $this->getKeyForRequest($holding, $group, $batchId);

            if (isset($this->requests[$key])) {
                continue;
            }

            $this->requests[$key]
            [$group->getId()]
            [$yardiBatchId]
            [Payment::formatType($settings->getPaymentTypeACH())] = array(
                self::REQUEST_FAILED => 0,
                self::REQUEST_SUCCESSFUL => 0,
            );

            $this->requests[$key]
            [$group->getId()]
            [$yardiBatchId]
            [Payment::formatType($settings->getPaymentTypeCC())] = array(
                self::REQUEST_FAILED => 0,
                self::REQUEST_SUCCESSFUL => 0,
            );

            $this->requests[$key]
            [$group->getId()]
            [$yardiBatchId]
            ['payment_batch_id'] = $batchId;
        }
    }

    /**
     * @param Holding $holding
     * @param $orders
     * @param $batchId
     */
    protected function saveSuccessfullRequest(Holding $holding, $orders, $yardiBatchId, $batchId)
    {
        $this->logMessage(
            sprintf(
                "Successfully Request holding: %s batch: %s",
                $holding->getId(),
                $yardiBatchId
            )
        );
        $this->fillRequestDefaultData($holding, $yardiBatchId, $batchId);
        /**
         * @var $order Order
         */
        foreach ($orders as $order) {
            $orderExternalApi = new OrderExternalApi();
            $orderExternalApi->setApiType(ExternalApi::YARDI);
            $orderExternalApi->setOrder($order);
            $orderExternalApi->setDepositDate($this->depositDate);

            $this->em->persist($orderExternalApi);

            $type = Payment::getType(
                $this->paymentClient->getSettings(),
                $order
            );

            $group = $order->getContract()->getGroup();
            $key = $this->getKeyForRequest($holding, $group, $batchId);
            $this->requests[$key][$group->getId()][$yardiBatchId][$type][self::REQUEST_SUCCESSFUL]++;
        }

        $this->em->flush();
    }

    /**
     * @param Holding $holding
     * @param $orders
     * @param $yardiBatchId
     */
    protected function saveFailedRequest(Holding $holding, $orders, $yardiBatchId, $batchId)
    {
        $this->logMessage(
            sprintf(
                "Failed Request holding: %s batch: %s",
                $holding->getId(),
                $yardiBatchId
            )
        );

        $this->fillRequestDefaultData($holding, $yardiBatchId, $batchId);
        /**
         * @var $order Order
         */
        foreach ($orders as $order) {
            $typePayment = Payment::getType(
                $this->paymentClient->getSettings(),
                $order
            );
            $group = $order->getContract()->getGroup();
            $key = $this->getKeyForRequest($holding, $group, $batchId);
            $this->requests[$key][$group->getId()][$yardiBatchId][$typePayment][self::REQUEST_FAILED]++;
        }
    }
}
