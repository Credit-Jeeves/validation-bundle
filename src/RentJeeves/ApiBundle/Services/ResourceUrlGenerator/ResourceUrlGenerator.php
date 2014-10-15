<?php

namespace RentJeeves\ApiBundle\Services\ResourceUrlGenerator;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\ApiBundle\Services\Encoders\AttributeEncoderInterface;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlGenerateMeta;
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

    protected $container;
    /**
     * @DI\InjectParams({
     *     "router"    = @DI\Inject("router"),
     *     "container" = @DI\Inject("service_container"),
     *     "metaReader" = @DI\Inject("api.resource_url_generator.meta_reader")
     * })
     */
    public function __construct(Router $router, Container $container, MetaReaderInterface $metaReader)
    {
        $this->router = $router;
        $this->container = $container;
        $this->metaReader = $metaReader;
    }

    public function generate($resource)
    {
        $config = $this->setup($resource);

        return $this->generateUrl($config, $resource);
    }

    /**
     * @param $resource
     * @return UrlGenerateMeta
     * @throws UrlGenerateException
     */
    protected function setup($resource)
    {
        $config = $this->metaReader->read($resource);

        if (!$config) {
            throw new UrlGenerateException("Resource doesn't have configuration @UrlGenerateMeta");
        }

        ($config->prefix !== null) || $config->prefix = $this->defaultPrefix;

        return $config;
    }

    protected function generateUrl(UrlGenerateMeta $config, $resource)
    {
        $getter = 'get' . ucfirst($config->id);

        return $this->router->generate(
            $config->prefix . $config->actionName,
            [$config->id => $this->encodedId($resource->$getter(), $config->encoder)]
        );
    }

    protected function encodedId($id, $encoderConfig = null)
    {
        if ($encoder = $this->getEncoder($encoderConfig)) {
            return $encoder->encode($id);
        }

        return $id;
    }

    protected function getEncoder($encoderConfig)
    {
        if ($encoderConfig) {
            $encoderServiceId = is_array($encoderConfig) ? array_shift($encoderConfig) : $encoderConfig;

            if ($this->container->has($encoderServiceId)) {
                $encoder = $this->container->get($encoderServiceId);

                $parameters = $encoderConfig;

                if (is_array($parameters)) {
                    foreach ($parameters as $name => $values) {
                        $encoder->$name = $values;
                    }
                }

                if ($encoder instanceof AttributeEncoderInterface) {
                    return $encoder;
                }
            }
        }

        return null;
    }
}
