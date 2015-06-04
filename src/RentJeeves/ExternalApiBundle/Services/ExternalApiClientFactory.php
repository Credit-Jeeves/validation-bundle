<?php

namespace RentJeeves\ExternalApiBundle\Services;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use RentJeeves\ExternalApiBundle\Services\MRI\MRIClient;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;

/**
 * @DI\Service("accounting.api_client.factory")
 */
class ExternalApiClientFactory
{
    /**
     * @var SoapClientFactory
     */
    protected $soapClientFactory;

    /**
     * @DI\InjectParams({
     *     "resManClient" = @DI\Inject("resman.client"),
     *     "mriClient"    = @DI\Inject("mri.client"),
     *     "soapClientFactory"  = @DI\Inject("soap.client.factory")
     * })
     */
    public function __construct(ResManClient $resManClient, MRIClient $mriClient, SoapClientFactory $soapClientFactory)
    {
        $this->accountingServiceClientMap[ApiIntegrationType::RESMAN] = $resManClient;
        $this->accountingServiceClientMap[ApiIntegrationType::MRI] = $mriClient;
        $this->soapClientFactory = $soapClientFactory;
    }

    /**
     * @param  string            $accountingType
     * @param  SettingsInterface $accountingSettings
     * @return ClientInterface
     */
    public function createClient($accountingType, SettingsInterface $accountingSettings)
    {
        if (array_key_exists($accountingType, SoapClientEnum::$realTimePaymentClientMapping)
            && empty($this->accountingServiceClientMap[$accountingType])
        ) {
            $serviceName = SoapClientEnum::$realTimePaymentClientMapping[$accountingType];
            $clientService = $this->soapClientFactory->getClient(
                $accountingSettings,
                $serviceName
            );
            $this->accountingServiceClientMap[$accountingType] = $clientService;
        }

        if (empty($this->accountingServiceClientMap[$accountingType])) {
            throw new \Exception(sprintf('Can\'t map service "%s" in factory', $accountingType));
        }

        /** @var ClientInterface $client */
        $client = $this->accountingServiceClientMap[$accountingType];

        $client->setSettings($accountingSettings);

        return $client;
    }
}
