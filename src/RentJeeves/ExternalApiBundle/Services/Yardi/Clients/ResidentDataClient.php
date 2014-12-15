<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;


use RentJeeves\CoreBundle\DateTime;

class ResidentDataClient extends AbstractClient
{
    protected $mapping = array(
        'GetResidentData' => array(
            self::MAPPING_FIELD_STD_CLASS    => 'GetResidentDataResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetResidentDataResponse',
        ),
        'GetResidents' => array(
            self::MAPPING_FIELD_STD_CLASS    => 'GetResidentsResult',
            self::MAPPING_DESERIALIZER_CLASS => 'GetResidentsResponse',
        ),
    );

    public function getResidents($propertyId)
    {
        $parameters = array(
            'GetResidents' => array_merge(
                $this->getLoginCredentials(),
                [
                    'YardiPropertyId' => $propertyId
                ]
            )
        );

        return $this->sendRequest(
            'GetResidents',
            $parameters
        );
    }

    public function getResidentData($propertyId, $residentId)
    {
        $parameters = array(
            'GetResidentData' => array_merge(
                $this->getLoginCredentials(),
                [
                    'YardiPropertyId' => $propertyId,
                    'TenantCode' => $residentId,
                    'IncludeLedger' => true,
                    'LedgerAsOfDate' => new DateTime(),
                    'IncludeLeaseCharges' => false,
                    'IncludeVehicleInfo' => false,
                    'IncludeRoommateData' => false,
                    'IncludeEmployerData' =>false,
                ]
            )
        );

        return $this->sendRequest(
            'GetResidentData',
            $parameters
        );
    }
}
