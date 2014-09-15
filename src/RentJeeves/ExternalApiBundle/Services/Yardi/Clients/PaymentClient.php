<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;

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
    );

    /**
     * @param DateTime $depositDate
     * @param string $yardiPropertyId
     * @param null $batchDescription
     * @param null $depositMemo
     *
     * @return integer|null
     */
    public function openReceiptBatchDepositDate(
        DateTime $depositDate,
        $yardiPropertyId,
        $batchDescription = null,
        $depositMemo = null
    ) {

        $parameters = array(
            'OpenReceiptBatch_DepositDate' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'YardiPropertyId'   => $yardiPropertyId,
                    'BatchDescription'  => $batchDescription,
                    'DepositDate'       => $depositDate,
                    'DepositMemo'       => $depositMemo
                )
            ),
        );

        return $this->processRequest(
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
        $parameters = array(
            'PostReceiptBatch' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'BatchId'   => (int) $batchId,
                )
            ),
        );

        $result = $this->processRequest(
            'PostReceiptBatch',
            $parameters
        );

        if ($result instanceof Messages) {
            return true;
        }

        return false;
    }
}
