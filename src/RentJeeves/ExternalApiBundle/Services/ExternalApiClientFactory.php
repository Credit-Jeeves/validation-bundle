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
     * @var array
     */
    protected $externalSoapClients = [
        ApiIntegrationType::YARDI_VOYAGER => SoapClientEnum::YARDI_PAYMENT,
        ApiIntegrationType::AMSI => SoapClientEnum::AMSI_LEDGER
    ];

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
        $uniqueClientKey = sprintf('Type:%s->ID:%s', $accountingType, $accountingSettings->getId());

        if (isset($this->accountingServiceClientMap[$uniqueClientKey])) {
            return $this->accountingServiceClientMap[$uniqueClientKey];
        }

        if (array_key_exists($accountingType, $this->externalSoapClients)
            && empty($this->accountingServiceClientMap[$uniqueClientKey])
        ) {
            $serviceName = $this->externalSoapClients[$accountingType];
            $clientService = $this->soapClientFactory->getClient(
                $accountingSettings,
                $serviceName
            );
            $this->accountingServiceClientMap[$uniqueClientKey] = $clientService;

            return $clientService;
        }

        if (!isset($this->accountingServiceClientMap[$uniqueClientKey]) &&
            isset($this->accountingServiceClientMap[$accountingType])) {
            $this->accountingServiceClientMap[$uniqueClientKey] = $this->accountingServiceClientMap[$accountingType];
        }

        if (empty($this->accountingServiceClientMap[$uniqueClientKey])) {
            throw new \Exception(sprintf('Can\'t map service "%s" in factory', $uniqueClientKey));
        }

        /** @var ClientInterface $client */
        $client = $this->accountingServiceClientMap[$uniqueClientKey];

        $client->setSettings($accountingSettings);

        return $client;
    }
}
