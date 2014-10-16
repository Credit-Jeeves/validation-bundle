<?php

namespace RentJeeves\ApiBundle\Services\Encoders;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\Container;

/**
 * @DI\Service("encoder_factory")
 */
class EncoderFactory
{
    /**
     * @var Container
     * @DI\Inject("service_container")
     */
    public $container;

    /**
     * @param $encoderConfig
     * @return null|AttributeEncoderInterface
     */
    public function getEncoder($encoderConfig)
    {
        if ($encoderConfig) {
            $encoderServiceId = is_array($encoderConfig) ? array_shift($encoderConfig) : $encoderConfig;

            if ($this->container->has($encoderServiceId)) {
                $encoder = $this->container->get($encoderServiceId);

                if ($encoder instanceof AttributeEncoderInterface) {

                    $parameters = $encoderConfig;

                    if (is_array($parameters)) {
                        foreach ($parameters as $name => $values) {
                            $encoder->$name = $values;
                        }
                    }

                    return $encoder;
                }
            }
        }

        return null;
    }
}
