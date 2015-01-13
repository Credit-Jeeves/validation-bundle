<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentDataClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionsLoginResponse;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\YardiClientEnum;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;

/**
 * @DI\Service("yardi.resident_data")
 */
class ResidentDataManager
{
    const CURRENT_RESIDENT = 'Current';

    /**
     * @var SoapClientFactory
     */
    protected $clientFactory;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @DI\InjectParams({
     *     "clientFactory" = @DI\Inject("soap.client.factory"),
     *     "exceptionCatcher" = @DI\Inject("fp_badaboom.exception_catcher")
     * })
     */
    public function __construct(SoapClientFactory $clientFactory, ExceptionCatcher $exceptionCatcher)
    {
        $this->clientFactory = $clientFactory;
        $this->exceptionCatcher = $exceptionCatcher;
    }

    public function getResidents(Holding $holding, Property $property)
    {
        $residentClient = $this->getApiClient($holding);
        $propertyMapping = $property->getPropertyMappingByHolding($holding);
        if (empty($propertyMapping)) {
            throw new \Exception(
                sprintf(
                    "PropertyID '%s', don't have external ID",
                    $property->getId()
                )
            );
        }

        $residents = $residentClient->getResidents($propertyMapping->getExternalPropertyId());

        return $residents->getPropertyResidents()->getResidents()->getResidents();
    }

    public function getCurrentResidents(Holding $holding, Property $property)
    {
        $residents = $this->getResidents($holding, $property);

        $currentResidents = array_filter(
            $residents,
            function ($resident) {
                return $resident->getStatus() == self::CURRENT_RESIDENT;
            }
        );

        return $currentResidents;
    }

    public function getResidentData(Holding $holding, Property $property, $residentId)
    {
        $propertyId = $property->getPropertyMapping()->first()->getExternalPropertyId();
        $residentClient = $this->getApiClient($holding);
        $resident = $residentClient->getResidentData($propertyId, $residentId);

        return $resident->getLeaseFiles()->getLeaseFile();
    }

    /**
     * @param Holding $holding
     * @param $externalPropertyId
     *
     * @return GetResidentTransactionsLoginResponse
     */
    public function getResidentTransactions(Holding $holding, $externalPropertyId)
    {
        $client = $this->getApiClient($holding, YardiClientEnum::RESIDENT_TRANSACTIONS);

        return $client->getResidentTransactions($externalPropertyId);
    }

    /**
     * @param Holding $holding
     * @return ResidentDataClient
     */
    protected function getApiClient(Holding $holding, $client = YardiClientEnum::RESIDENT_DATA)
    {
        return $this->clientFactory->getClient(
            $holding->getYardiSettings(),
            $client
        );
    }
}
