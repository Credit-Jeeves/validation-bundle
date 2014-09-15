<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\CoreBundle\DateTime;

class PaymentClient extends AbstractClient
{
    /**
     * @param DateTime $depositDate
     * @param string $yardiPropertyId
     * @param null $batchDescription
     * @param null $DepositMemo
     * @return mixed
     */
    public function openReceiptBatchDepositDate(
        DateTime $depositDate,
        $yardiPropertyId,
        $batchDescription = null,
        $DepositMemo = null
    ) {

        $parameters = array(
            'OpenReceiptBatch_DepositDate' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'YardiPropertyId'   => $yardiPropertyId,
                    'BatchDescription'  => $batchDescription,
                    'DepositDate'       => $depositDate,
                    'DepositMemo'       => $DepositMemo
                )
            ),
        );

        return $this->processRequest(
            'OpenReceiptBatch_DepositDate',
            $parameters,
            'OpenReceiptBatch_DepositDateResult'
        );
    }
}
