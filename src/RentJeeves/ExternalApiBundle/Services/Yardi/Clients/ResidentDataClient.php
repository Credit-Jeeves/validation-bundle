<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;


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

        return $this->processRequest(
            'GetResidents',
            $parameters
        );
    }
}
