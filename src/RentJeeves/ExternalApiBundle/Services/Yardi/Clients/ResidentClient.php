<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;

class ResidentClient extends AbstractClient
{
    /**
     * This method don't need any parameters, from outside
     * so it can be used for checking login
     *
     * @return GetPropertyConfigurationsResponse|null
     */
    public function getPropertyConfigurations()
    {
        $parameters = array(
            'GetPropertyConfigurations' => $this->getLoginCredentials()
        );

        return $this->processRequest(
            'GetPropertyConfigurations',
            $parameters,
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse'
        );
    }

    public function getResidentData()
    {
    }
}
