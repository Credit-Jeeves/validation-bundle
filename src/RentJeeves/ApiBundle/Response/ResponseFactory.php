<?php

namespace RentJeeves\ApiBundle\Response;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use JMS\DiExtraBundle\Annotation as DI;
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

    public function getResponse($entity)
    {
        $reflectClass = new ReflectionClass($entity);

        $responseClassName = __NAMESPACE__ . '\\' . $reflectClass->getShortName();

        if (class_exists($responseClassName)) {
            $response = new $responseClassName;

            if ($response instanceof ResponseResource and $response = $this->getResponseResourceServiceId($response)) {
                $response->setEntity($entity);
            }

            return $response;
        }

        throw new InvalidResponseResourceException('Response not exist.');
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
