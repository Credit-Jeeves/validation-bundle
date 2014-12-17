<?php

namespace RentJeeves\ExternalApiBundle\Soap;

use RentJeeves\ExternalApiBundle\Services\ClientsEnum\YardiClientEnum;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Exception;

class SoapClientFactory
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getClient(SettingsInterface $settings, $type, $debug = false)
    {
        if (!in_array($type, YardiClientEnum::all())) {
            throw new Exception("Such client({$type}) does not exist");
        }

        $client = $this->container->get($type);
        $client->setDebug($debug);
        $client->setSettings($settings);
        $client->build();
        return $client;
    }
}
