<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;

class ResidentTransactionsClient extends AbstractClient
{
    protected $mapping = array(
        'GetPropertyConfigurations' => array(
            self::MAPPING_FIELD_STD_CLASS    => 'GetPropertyConfigurationsResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetPropertyConfigurationsResponse',
        ),
        'GetResidentTransactions_Login' => array(
            self::MAPPING_FIELD_STD_CLASS    => 'GetResidentTransactions_LoginResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetResidentTransactionsLoginResponse',
        ),
        'GetVersionNumber' => array(
            self::MAPPING_FIELD_STD_CLASS    => 'GetVersionNumberResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetVersionNumberResponse',
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

    public function getResidentTransactions($propertyId)
    {
        $parameters = array(
            'GetResidentTransactions_Login' => array_merge(
                $this->getLoginCredentials(),
                [
                    'YardiPropertyId' => $propertyId
                ]
            )
        );

        return $this->processRequest(
            'GetResidentTransactions_Login',
            $parameters
        );
    }

    public function getVersionNumber()
    {
        $parameters = ['GetVersionNumber' => []];

        return $this->processRequest(
            'GetVersionNumber',
            $parameters
        );
    }
}
