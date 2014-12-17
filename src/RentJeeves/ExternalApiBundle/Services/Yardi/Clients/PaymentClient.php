<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use \DateTime;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use SoapVar;

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
     * @param DateTime $depositDate
     * @param string $yardiPropertyId
     * @param null $batchId
     * @param null $depositMemo
     *
     * @return integer|null
     */
    public function openReceiptBatchDepositDate(
        DateTime $depositDate,
        $yardiPropertyId,
        $batchId = null,
        $depositMemo = null
    ) {
        $this->debugMessage('Run OpenReceiptBatch_DepositDate');
        $parameters = array(
            'OpenReceiptBatch_DepositDate' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'YardiPropertyId'   => $yardiPropertyId,
                    'BatchDescription'  => 'RentTrack Batch #' . $batchId,
                    'DepositDate'       => $depositDate,
                    'DepositMemo'       => $depositMemo
                )
            ),
        );

        return $this->sendRequest(
            'OpenReceiptBatch_DepositDate',
            $parameters
        );
    }

    /**
     * @param $batchId
     *
     * @return boolean
     */
    public function closeReceiptBatch($batchId)
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
