<?php

namespace RentJeeves\ExternalApiBundle\Services;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use RentJeeves\ExternalApiBundle\Services\MRI\MRIClient;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;

/**
 * @DI\Service("accounting.api_client.factory")
 */
class ExternalApiClientFactory
{
    /**
     * @var SettingsInterface
     */
    protected $settings;

    /**
     * @DI\InjectParams({
     *     "resManClient" = @DI\Inject("resman.client"),
     *     "mriClient"    = @DI\Inject("mri.client")
     * })
     */
    public function __construct(ResManClient $resManClient, MRIClient $mriClient)
    {
        $this->accountingServiceClientMap[ApiIntegrationType::RESMAN] = $resManClient;
        $this->accountingServiceClientMap[ApiIntegrationType::MRI] = $mriClient;
    }

    /**
     * @param string $accountingType
     * @return ClientInterface
     */
    public function createClient($accountingType)
    {
        if (empty($this->accountingServiceClientMap[$accountingType])) {
            throw new \Exception("Can't map service '{$accountingType}' in factory");
        }

        /** @var ClientInterface $client */
        $client = $this->accountingServiceClientMap[$accountingType];

        !$this->settings || $client->setSettings($this->settings);

        return $client;
    }

    /**
     * @param SettingsInterface $settings
     * @return $this
     */
    public function setSettings(SettingsInterface $settings)
    {
        $this->settings = $settings;

        return $this;
    }
}
