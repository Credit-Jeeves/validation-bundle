<?php

namespace RentJeeves\ExternalApiBundle\Services;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("accounting.api_client.factory")
 */
class ExternalApiClientFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SettingsInterface
     */
    protected $settings;

    /**
     * @var array
     */
    protected $accountingServiceClientMap = [
        ApiIntegrationType::RESMAN => 'resman.client'
    ];

    /**
     * @param ContainerInterface $container
     * @DI\InjectParams({
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $accountingType
     * @return ClientInterface
     */
    public function createClient($accountingType)
    {
        if (empty($this->accountingServiceClientMap[$accountingType]) ||
            !$this->container->has($this->accountingServiceClientMap[$accountingType])
        ) {
          return null;
        }

        /** @var ClientInterface $client */
        $client = $this->container->has($this->accountingServiceClientMap[$accountingType]);

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
