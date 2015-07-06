<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\SynchronizationStrategy;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\ExternalApiBundle\Services\Yardi\YardiXmlCleaner;
use SoapVar;
use RentJeeves\ExternalApiBundle\Model\ResidentTransactions;

class PaymentClient extends AbstractClient
{
    protected $mapping = array(
        'OpenReceiptBatch_DepositDate' => array(
            self::MAPPING_FIELD_STD_CLASS       => 'OpenReceiptBatch_DepositDateResult',
            self::MAPPING_DESERIALIZER_CLASS    =>  null,
        ),
        'PostReceiptBatch' => array(
            self::MAPPING_FIELD_STD_CLASS       => 'PostReceiptBatchResult',
            self::MAPPING_DESERIALIZER_CLASS    => 'Messages',
        ),
        'AddReceiptsToBatch' => array(
            self::MAPPING_FIELD_STD_CLASS       => 'AddReceiptsToBatchResult',
            self::MAPPING_DESERIALIZER_CLASS    => 'Messages',
        ),
        'CancelReceiptBatch' => array(
            self::MAPPING_FIELD_STD_CLASS       => 'CancelReceiptBatchResult',
            self::MAPPING_DESERIALIZER_CLASS    => 'Messages',
        )
    );

    /**
     * @param string $externalPropertyId
     * @param \DateTime $paymentBatchDate
     * @param string $description
     *
     * @return integer|null
     */
    public function openBatch(
        $yardiPropertyId,
        \DateTime $depositDate,
        $description
    ) {
        $this->debugMessage('Run OpenReceiptBatch_DepositDate');
        /** @var YardiSettings $settings */
        $settings = $this->getSettings();
        $synchStrategy = $settings->getSynchronizationStrategy();
        $parameters = [
            'YardiPropertyId'   => $yardiPropertyId,
            'BatchDescription'  => $description,
            'DepositMemo'       => null
        ];

        if ($synchStrategy === SynchronizationStrategy::DEPOSITED) {
            $parameters['DepositDate'] = $depositDate;
        }

        $parametersForRequest = [
            'OpenReceiptBatch_DepositDate' => array_merge(
                $this->getLoginCredentials(),
                $parameters
            )
        ];

        return $this->sendRequest(
            'OpenReceiptBatch_DepositDate',
            $parametersForRequest
        );
    }

    /**
     * Why strange method name, which can be confusing, described:
     * @link https://credit.atlassian.net/browse/RT-813?jql=text%20~%20%22PostReceiptBatch%22
     *
     * @param $batchId
     *
     * @return boolean
     */
    public function closeBatch($batchId)
    {
        $this->debugMessage('Run PostReceiptBatch');
        $parameters = array(
            'PostReceiptBatch' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'BatchId'   => (int) $batchId,
                )
            ),
        );

        $result = $this->sendRequest(
            'PostReceiptBatch',
            $parameters
        );

        if ($result instanceof Messages) {
            return true;
        }

        return false;
    }

    /**
     * @param Order $order
     * @param string $externalPropertyId
     * @return boolean|string
     */
    public function addPaymentToBatch(Order $order, $externalPropertyId)
    {
        if (!$externalLeaseId = $order->getContract()->getExternalLeaseId()) {
            $this->logger->alert(
                sprintf(
                    'Order(ID:%s) will not send to Yardi, because his contract(ID:%s) does not have externalLeaseId.',
                    $order->getId(),
                    $order->getContract()->getId()
                )
            );

            return false;
        }

        $orders = new ArrayCollection([$order]);
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('soapYardiRequest');
        $residentTransactions = new ResidentTransactions(
            $this->getSettings(),
            $orders
        );
        $xml = $this->serializer->serialize(
            $residentTransactions,
            'xml',
            $context
        );
        $xml = YardiXmlCleaner::prepareXml($xml);

        $result = $this->addReceiptsToBatch($order->getBatchId(), $xml);

        if ($result instanceof Messages) {
            return $result->getMessage()->getMessage();
        }

        return false;
    }

    /**
     * @param $batchId
     * @param $xml
     *
     * @return mixed
     */
    public function addReceiptsToBatch($batchId, $xml)
    {
        $this->debugMessage('Run AddReceiptsToBatch');
        $parameters = array(
            'AddReceiptsToBatch' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'BatchId'           => $batchId,
                    'TransactionXml'    => new SoapVar($xml, 147),
                )
            ),
        );

        return $this->sendRequest(
            'AddReceiptsToBatch',
            $parameters
        );
    }

    public function cancelReceiptBatch($batchId)
    {
        $this->debugMessage('Run CancelReceiptBatch');
        $parameters = array(
            'CancelReceiptBatch' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'BatchId'           => $batchId,
                )
            ),
        );

        return $this->sendRequest(
            'CancelReceiptBatch',
            $parameters
        );
    }
}
