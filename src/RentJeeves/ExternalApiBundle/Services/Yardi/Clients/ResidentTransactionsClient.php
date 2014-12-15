<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use DateTime;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionLoginResponse;
use SoapVar;

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
        'ImportResidentTransactions_DepositDate' => array(
            self::MAPPING_FIELD_STD_CLASS    => 'ImportResidentTransactions_DepositDateResult',
            self::MAPPING_DESERIALIZER_CLASS => 'Messages',
        ),
        'ImportResidentTransactions_Login' => array(
            self::MAPPING_FIELD_STD_CLASS    => 'ImportResidentTransactions_LoginResult',
            self::MAPPING_DESERIALIZER_CLASS => 'Messages',
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

        return $this->sendRequest(
            'GetPropertyConfigurations',
            $parameters
        );
    }

    /**
     * @param string $propertyId
     * @return GetResidentTransactionLoginResponse
     */
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

        return $this->sendRequest(
            'GetResidentTransactions_Login',
            $parameters
        );
    }

    public function getVersionNumber()
    {
        $parameters = ['GetVersionNumber' => []];

        return $this->sendRequest(
            'GetVersionNumber',
            $parameters
        );
    }

    // Could not get a successful result with this method.
    public function importResidentTransactionsDepositDate($transactionXml, DateTime $depositDate, $depositMemo = null)
    {
        $parameters = array(
            'ImportResidentTransactions_DepositDate' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'TransactionXml' => new SoapVar($transactionXml, 147),
                    'DepositDate' => $depositDate,
                    'DepositMemo' => $depositMemo,
                )
            ),
        );

        return $this->sendRequest(
            'ImportResidentTransactions_DepositDate',
            $parameters
        );
    }

    public function importResidentTransactionsLogin($transactionXml)
    {
        $parameters = array(
            'ImportResidentTransactions_Login' => array_merge(
                $this->getLoginCredentials(),
                array(
                    'TransactionXml' => new SoapVar($transactionXml, 147)
                )
            ),
        );

        return $this->sendRequest(
            'ImportResidentTransactions_Login',
            $parameters
        );
    }
}
