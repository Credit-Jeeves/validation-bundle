<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;

class ResidentClient extends AbstractClient
{
    protected $mapping = array(
        'GetPropertyConfigurations' => array(
            self::MAPPING_FIELD_STD_CLASS    => 'GetPropertyConfigurationsResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetPropertyConfigurationsResponse',
        ),
    );

    /**
     * This method don't need any parameters, from outside
     * so it can be used for checking login
     *
     * @return GetPropertyConfigurationsResponse|null
     */
    public function getPropertyConfigurations()
    {
        $this->debugMessage('Run getPropertyConfigurations');
        $parameters = array(
            'GetPropertyConfigurations' => $this->getLoginCredentials()
        );

        return $this->processRequest(
            'GetPropertyConfigurations',
            $parameters
        );
    }
}
