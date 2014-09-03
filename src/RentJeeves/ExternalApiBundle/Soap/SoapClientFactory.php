<?php

namespace RentJeeves\ExternalApiBundle\Soap;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \Exception;

class SoapClientFactory
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getClient(SoapSettingsInterface $settings, $type)
    {
        if (!in_array($type, SoapClientEnum::all())) {
            throw new Exception("Such client({$type}) does not exist");
        }

        $client = $this->container->get($type);
        $client->setSettings($settings);
        $client->build();

        return $client;
    }
}
