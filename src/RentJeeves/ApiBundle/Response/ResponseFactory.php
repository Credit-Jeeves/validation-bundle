<?php

namespace RentJeeves\ApiBundle\Response;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\ApiBundle\Response\Exception\InvalidResponseResourceException;
use Symfony\Component\DependencyInjection\Container;

/**
 * @DI\Service("response_resource.factory")
 */
class ResponseFactory
{
    /**
     * @var Container
     * @DI\Inject("service_container")
     */
    public $container;

    /**
     * @param $entity
     * @return ResponseResource|null
     * @throws InvalidResponseResourceException
     */
    public function getResponse($entity)
    {
        if (!is_object($entity)) {
            return null;
        }

        $reflectClass = new ReflectionClass($entity);

        $responseClassName = __NAMESPACE__ . '\\' . $reflectClass->getShortName();

        if (class_exists($responseClassName)) {
            $response = new $responseClassName;

            if ($response instanceof ResponseResource and $response = $this->getResponseResourceServiceId($response)) {
                $response->setEntity($entity);
            }

            return $response;
        }

        throw new InvalidResponseResourceException(
            sprintf('Response entity "%s" does not exist.', $reflectClass->getShortName())
        );
    }

    protected function getResponseResourceServiceId($response)
    {
        /** @var Reader $annotationReader */
        $annotationReader = $this->container->get('annotation_reader');
        $annotationName = 'JMS\\DiExtraBundle\\Annotation\\Service';
        /** @var DI\Service $annotation */
        $annotation = $annotationReader->getClassAnnotation(new ReflectionClass($response), $annotationName);

        if ($annotation && $this->container->has($annotation->id)) {
            return $this->container->get($annotation->id);
        }

        throw new InvalidResponseResourceException('Response must contain @Service annotation.');
    }
}
