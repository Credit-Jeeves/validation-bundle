<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentDataClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionsLoginResponse;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;

/**
 * @DI\Service("yardi.resident_data")
 */
class ResidentDataManager
{
    const CURRENT_RESIDENT = 'Current';

    const NOTICE_RESIDENT = 'Notice';

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

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @return array
     * @throws \Exception
     */
    public function getResidents(Holding $holding, $externalPropertyId)
    {
        $residentClient = $this->getApiClient($holding);
        $residents = $residentClient->getResidents($externalPropertyId);

        return $residents->getPropertyResidents()->getResidents()->getResidents();
    }

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @return array
     * @throws \Exception
     */
    public function getCurrentAndNoticesResidents(Holding $holding, $externalPropertyId)
    {
        $residents = $this->getResidents($holding, $externalPropertyId);

        $currentResidents = array_filter(
            $residents,
            function ($resident) {
                return $resident->getStatus() == self::CURRENT_RESIDENT ||
                $resident->getStatus() == self::NOTICE_RESIDENT;
            }
        );

        return $currentResidents;
    }

    /**
     * @param Holding $holding
     * @param string $residentId
     * @param string $externalPropertyId
     * @return ResidentLeaseFile
     * @throws \Exception
     */
    public function getResidentData(Holding $holding, $residentId, $externalPropertyId)
    {
        $residentClient = $this->getApiClient($holding);
        $resident = $residentClient->getResidentData($externalPropertyId, $residentId);

        if (empty($resident) || !$resident->getLeaseFiles()) {
            throw new \Exception(
                sprintf(
                    "Can't get resident data by resident ID '%s' and external property ID '%s'",
                    $residentId,
                    $externalPropertyId
                )
            );
        }

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
        $client = $this->getApiClient($holding, SoapClientEnum::YARDI_RESIDENT_TRANSACTIONS);

        return $client->getResidentTransactions($externalPropertyId);
    }

    /**
     * @param Holding $holding
     * @return ResidentDataClient
     */
    protected function getApiClient(Holding $holding, $client = SoapClientEnum::YARDI_RESIDENT_DATA)
    {
        return $this->clientFactory->getClient(
            $holding->getYardiSettings(),
            $client
        );
    }
}
