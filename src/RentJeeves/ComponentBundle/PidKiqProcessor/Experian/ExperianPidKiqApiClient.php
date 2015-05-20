<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor\Experian;

use CreditJeeves\ExperianBundle\Model\NetConnectRequest;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\InvalidResponseException;

class ExperianPidKiqApiClient extends ExperianBaseApiClient
{
    /**
     * @param NetConnectRequest $request
     * @return NetConnectResponse
     */
    public function getQuestions(NetConnectRequest $request)
    {
        return $this->doRequest($request, 'PreciseID');
    }

    /**
     * @param NetConnectRequest $request
     * @return NetConnectResponse
     */
    public function getResult(NetConnectRequest $request)
    {
        return $this->doRequest($request, 'PreciseIDQuestions');
    }

    /**
     * {@inheritdoc}
     */
    protected function validateResponse(NetConnectResponse $netConnectResponse)
    {
        $products = $netConnectResponse->getProducts();
        if (!$products) {
            throw new InvalidResponseException("Don't have 'Products' in response");
        }

        $preciseIDServer = $products->getPreciseIDServer();
        if ($preciseIDServer->getError()->getErrorCode()) {
            throw new InvalidResponseException(
                $preciseIDServer->getError()->getErrorDescription(),
                $preciseIDServer->getError()->getErrorCode()
            );
        }
    }
}
