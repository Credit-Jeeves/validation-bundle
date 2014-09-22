<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\SerializationContext;
use \DateTime;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\OrderExternalApi;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\ExternalApi;
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
class ReceiptBatch
{
    const LIMIT_ORDERS = 500;

    const LIMIT_HOLDING = 50;

    const REQUEST_SUCCESSFULLY = 'successfully';

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
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;
    /**
     * @InjectParams({
     *     "em"                 = @Inject("doctrine.orm.default_entity_manager"),
     *     "clientFactory"      = @Inject("soap.client.factory"),
     *     "serializer"         = @Inject("jms_serializer"),
     *     "mailer"             = @Inject("project.mailer"),
     *     "exceptionCatcher"   = @Inject("fp_badaboom.exception_catcher")
     * })
     */
    public function __construct(
        EntityManager $em,
        SoapClientFactory $clientFactory,
        Serializer $serializer,
        Mailer $mailer,
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
     * @param DateTime $depositDate
     */
    public function run(DateTime $depositDate = null)
    {
        if (!$depositDate) {
            $depositDate = new DateTime();
        }

        $this->depositDate = $depositDate;
        $startPagination = 1;
        $this->logMessage("Deposit date: ".$depositDate->format('Y-m-d'));
        try {
            while (
                $holdings = $this->getHoldingRepository()->findHoldingsWhithYardiSettings(
                    $startPagination,
                    self::LIMIT_HOLDING
                )
            ) {
                $this->processHolding($holdings);
                $startPagination += self::LIMIT_HOLDING;
                $this->em->clear();
                gc_collect_cycles();
            }
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->logMessage(sprintf("Failed %s", $e->getMessage()));
        }
    }

    /**
     * @param $holdings
     */
    protected function processHolding($holdings)
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
            $this->processRemotePropertyId($holding);
            $this->finishProcessHolding($holding);
        }
    }

    /**
     * @param Holding $holding
     */
    protected function processRemotePropertyId(Holding $holding)
    {
        $startPagination = 1;
        while (
            $orders = $this->getOrderRepository()->getReceiptBatch(
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
                $property = $order->getContract()->getProperty();
                $remotePropertyId = $property->getPropertyMapping()->getLandlordPropertyId();
                $description = $property->getFullAddress();

                try {
                    $this->processOrders($holding, $remotePropertyId, $description);
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
     * @param $remotePropertyId
     * @param null $description
     */
    protected function processOrders(Holding $holding, $remotePropertyId, $description = null)
    {
        $startPagination = 1;
        while (
            $ordersReceiptBatch = $this->getOrder()->getReceiptBatch(
                $this->depositDate,
                $holding,
                $startPagination,
                self::LIMIT_ORDERS,
                $remotePropertyId
            )
        ) {
            try {
                $result = $this->execute(
                    $ordersReceiptBatch,
                    $this->getBatchId($remotePropertyId, $description)
                );

                if ($result === true) {
                    $this->successfullRequest($holding, $ordersReceiptBatch);
                } else {
                    $this->failedRequest($holding, $ordersReceiptBatch);
                }
            } catch (Exception $e) {
                $this->failedRequest($holding, $ordersReceiptBatch);
                throw $e;
            }

            $startPagination += self::LIMIT_ORDERS;
        }
    }

    /**
     * @param array $orders
     * @param string $batchId
     */
    protected function execute($orders, $batchId)
    {
        $transaction = new ResidentTransactions($orders);
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('soapYardiRequest');
        $residentTransactions = new ResidentTransactions($orders);
        $xml = $this->serializer->serialize(
            $residentTransactions,
            'xml',
            $context
        );

        $this->paymentClient->addReceiptsToBatch(
            $batchId,
            $this->prepeaXml($xml)
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
     * @param null $description
     * @return int|null
     * @throws \Exception
     */
    protected function getBatchId($remotePropertyId, $description = null)
    {
        if (isset($this->batchIds[$remotePropertyId])) {
            return $this->batchIds[$remotePropertyId];
        }

        $batchId = $this->paymentClient->openReceiptBatchDepositDate(
            $this->depositDate,
            $remotePropertyId,
            $description
        );

        if ($this->paymentClient->isError()) {
            $this->throwExceptionClient(
                sprintf(
                    "Failed create batch for remote property Id: %s.",
                    $remotePropertyId
                )
            );
        }
        $this->batchIds[$remotePropertyId] = $batchId;
        return $batchId;
    }

    protected function throwExceptionClient($message)
    {
        $response = $this->paymentClient->getFullResponse($isShow = false);
        $request = $this->paymentClient->getFullRequest($isShow = false);
        $this->logMessage($this->paymentClient->getErrorMessage());

        throw new Exception(
            sprintf(
                $message."\nRequest: %s %s \n Response: %s %s",
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
    protected function prepeaXml($xml)
    {
        if (empty($xml)) {
            return $xml;
        }

        $domXml = new DOMDocument();
        $domXml->loadXML($xml);
        $xmlOut = $domXml->saveXML($domXml->documentElement);

        return $xmlOut;
    }

    protected function processRequest(Holding $holding)
    {
        if (!isset($this->requests[$holding->getId()])) {
            $this->requests[$holding->getId()] = array(
                self::REQUEST_FAILED => 0,
                self::REQUEST_SUCCESSFULLY => 0,
            );
        }
    }

    protected function successfullRequest(Holding $holding, $orders)
    {
        $countOrders = count($orders);
        $this->processRequest($holding);
        $currentSuccessfully = $this->requests[$holding->getId()][self::REQUEST_SUCCESSFULLY];
        $this->requests[$holding->getId()][self::REQUEST_SUCCESSFULLY] = $currentSuccessfully + $countOrders;

        /**
         * @var $order Order
         */
        foreach ($orders as $order) {
            $orderExternalApi = new OrderExternalApi();
            $orderExternalApi->setApiType(ExternalApi::YARDI);
            $orderExternalApi->setOrder($order);
            $order->setExternalApi($orderExternalApi);

            $this->em->persist($orderExternalApi);
            $this->em->persist($order);
        }

        $this->em->flush();
    }

    protected function failedRequest(Holding $holding, $orders)
    {
        $countOrders = count($orders);
        $this->processRequest($holding);
        $currentFailed = $this->requests[$holding->getId()][self::REQUEST_FAILED];
        $this->requests[$holding->getId()][self::REQUEST_FAILED] = $currentFailed + $countOrders;
    }

    protected function finishProcessHolding(Holding $holding)
    {
        //@TODO send email to landlord with report
    }
}
