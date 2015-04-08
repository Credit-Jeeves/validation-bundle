<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI\Clients;

class AMSILedgerClient extends AMSIBaseClient
{
    /**
     * {@inheritdoc}
     */
    public function canWorkWithBatches()
    {
        return true;
    }

    /**
     * @return array
     */
    protected function getLoginCredentials()
    {
        return [
            'UserID'  => $this->settings->getUser(),
            'Password'=> $this->settings->getPassword(),
            'PortfolioName' => $this->settings->getPortfolioName(),
            'Preliminary' => 0,
        ];
    }
}
