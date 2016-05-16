<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionLoginResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetUnitInformationResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseChargesLoginResponse;
use SoapVar;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;

class ResidentTransactionsClient extends AbstractClient
{
    protected $mapping = [
        'GetPropertyConfigurations' => [
            self::MAPPING_FIELD_STD_CLASS    => 'GetPropertyConfigurationsResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetPropertyConfigurationsResponse',
        ],
        'GetUnitInformation_Login' => [
            self::MAPPING_FIELD_STD_CLASS    => 'GetUnitInformation_LoginResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetUnitInformationResponse',
        ],
        'GetResidentTransactions_Login' => [
            self::MAPPING_FIELD_STD_CLASS    => 'GetResidentTransactions_LoginResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetResidentTransactionsLoginResponse',
        ],
        'GetVersionNumber' => [
            self::MAPPING_FIELD_STD_CLASS    => 'GetVersionNumberResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetVersionNumberResponse',
        ],
        'ImportResidentTransactions_DepositDate' => [
            self::MAPPING_FIELD_STD_CLASS    => 'ImportResidentTransactions_DepositDateResult',
            self::MAPPING_DESERIALIZER_CLASS => 'Messages',
        ],
        'ImportResidentTransactions_Login' => [
            self::MAPPING_FIELD_STD_CLASS    => 'ImportResidentTransactions_LoginResult',
            self::MAPPING_DESERIALIZER_CLASS => 'Messages',
        ],
        'GetResidentLeaseCharges_Login' => [
            self::MAPPING_FIELD_STD_CLASS    => 'GetResidentLeaseCharges_LoginResult',
            self::MAPPING_DESERIALIZER_CLASS => 'ResidentLeaseChargesLoginResponse',
        ],
    ];

    /**
     * This method don't need any parameters, from outside
     * so it can be used for checking login
     *
     * @return GetPropertyConfigurationsResponse|null
     */
    public function getPropertyConfigurations()
    {
        $this->debugMessage('Run getPropertyConfigurations');
        $parameters = [
            'GetPropertyConfigurations' => $this->getLoginCredentials()
        ];

        return $this->sendRequest(
            'GetPropertyConfigurations',
            $parameters
        );
    }


    /**
     * @param $propertyId
     * @return GetUnitInformationResponse|null
     * @throws \SoapFault
     */
    public function getUnitInformation($propertyId)
    {
        $this->debugMessage(sprintf('Run getUnitInformation with property id "%s"', $propertyId));

        $parameters = [
            'GetUnitInformation_Login' => array_merge(
                $this->getLoginCredentials(),
                [
                    'YardiPropertyId' => $propertyId,
                ]
            )
        ];

        return $this->sendRequest(
            'GetUnitInformation_Login',
            $parameters
        );
    }


    /**
     * @param string $propertyId
     * @return GetResidentTransactionLoginResponse|null
     */
    public function getResidentTransactions($propertyId)
    {
        $parameters = [
            'GetResidentTransactions_Login' => array_merge(
                $this->getLoginCredentials(),
                [
                    'YardiPropertyId' => $propertyId
                ]
            )
        ];

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
    public function importResidentTransactionsDepositDate($transactionXml, \DateTime $depositDate, $depositMemo = null)
    {
        $parameters = [
            'ImportResidentTransactions_DepositDate' => array_merge(
                $this->getLoginCredentials(),
                [
                    'TransactionXml' => new SoapVar($transactionXml, 147),
                    'DepositDate' => $depositDate,
                    'DepositMemo' => $depositMemo,
                ]
            ),
        ];

        return $this->sendRequest(
            'ImportResidentTransactions_DepositDate',
            $parameters
        );
    }

    /**
     * @param $transactionXml
     * @return Messages
     * @throws \Exception
     * @throws \SoapFault
     */
    public function importResidentTransactionsLogin($transactionXml)
    {
        $parameters = [
            'ImportResidentTransactions_Login' => array_merge(
                $this->getLoginCredentials(),
                [
                    'TransactionXml' => new SoapVar($transactionXml, 147)
                ]
            ),
        ];

        return $this->sendRequest(
            'ImportResidentTransactions_Login',
            $parameters
        );
    }

    /**
     * @param $externalPropertyId
     * @param \DateTime $postMonth
     * @return ResidentLeaseChargesLoginResponse|null
     * @throws \Exception
     * @throws \SoapFault
     */
    public function getResidentLeaseCharges($externalPropertyId, \DateTime $postMonth = null)
    {
        if (empty($postMonth)) {
            $postMonth = new \DateTime();
        }

        $parameters = [
            'GetResidentLeaseCharges_Login' => array_merge(
                $this->getLoginCredentials(),
                [
                    'YardiPropertyId' => $externalPropertyId,
                    'PostMonth' => $postMonth
                ]
            )
        ];

        return $this->sendRequest(
            'GetResidentLeaseCharges_Login',
            $parameters
        );
    }
}
