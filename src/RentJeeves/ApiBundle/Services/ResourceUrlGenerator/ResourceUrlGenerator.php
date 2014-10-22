<?php

namespace RentJeeves\ApiBundle\Services\ResourceUrlGenerator;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\ApiBundle\Services\Encoders\EncoderFactory;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;

/**
 * @DI\Service("api.resource_url_generator")
 */
class ResourceUrlGenerator
{
    protected $defaultPrefix = 'api_';

    protected $metaReader;

    protected $router;

    protected $encoderFactory;

    /**
     * @DI\InjectParams({
     *     "router"         = @DI\Inject("router"),
     *     "encoderFactory" = @DI\Inject("encoder_factory"),
     *     "metaReader"     = @DI\Inject("api.resource_url_generator.meta_reader")
     * })
     */
    public function __construct(Router $router, EncoderFactory $encoderFactory, MetaReaderInterface $metaReader)
    {
        $this->router = $router;
        $this->encoderFactory = $encoderFactory;
        $this->metaReader = $metaReader;
    }

    public function generate($resource)
    {
        $config = $this->getConfig($resource);

        return $this->router->generate(
            $config->prefix . $config->actionName,
            [$config->attributeName => $this->encodeAttribute($resource->{$config->attributeName}, $config->encoder)],
            true
        );
    }

    /**
     * @param $resource
     * @return UrlResourceMeta
     * @throws UrlGeneratorException
     */
    protected function getConfig($resource)
    {
        $config = $this->metaReader->read($resource);

        if (!$config) {
            throw new UrlGeneratorException("Resource doesn't have configuration @UrlResourceMeta");
        }

        ($config->prefix !== null) || $config->prefix = $this->defaultPrefix;

        return $config;
    }

    protected function encodeAttribute($attribute, $encoderConfig)
    {
        if ($encoder = $this->encoderFactory->getEncoder($encoderConfig)) {
            return $encoder->encode($attribute);
        }

        return $attribute;
    }
}
