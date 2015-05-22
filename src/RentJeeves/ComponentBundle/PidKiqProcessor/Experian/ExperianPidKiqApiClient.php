<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor\Experian;

use CreditJeeves\ExperianBundle\Model\NetConnectRequest;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\PidKiqInvalidResponseException;

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
        try {
            $products = $netConnectResponse->getProducts();
            if (!$products) {
                throw new PidKiqInvalidResponseException("Don't have 'Products' in response");
            }

            $preciseIDServer = $products->getPreciseIDServer();
            if ($preciseIDServer->getError()->getErrorCode()) {
                throw new PidKiqInvalidResponseException(
                    $preciseIDServer->getError()->getErrorDescription(),
                    $preciseIDServer->getError()->getErrorCode()
                );
            }
        } catch (PidKiqInvalidResponseException $e) {
            $this->logger->alert(
                sprintf('[Experian]Invalid Response: %s:%s', $e->getCode(), $e->getMessage())
            );
            throw $e;
        }
    }
}
