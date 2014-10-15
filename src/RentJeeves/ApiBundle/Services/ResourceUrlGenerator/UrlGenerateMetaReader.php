<?php

namespace RentJeeves\ApiBundle\Services\ResourceUrlGenerator;

use Doctrine\Common\Annotations\Reader;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("api.resource_url_generator.meta_reader")
 */
class UrlGenerateMetaReader implements MetaReaderInterface
{
    protected $annotationReader;

    protected $annotationName = 'RentJeeves\\ApiBundle\\Services\\ResourceUrlGenerator\\Annotation\\UrlGenerateMeta';

    /**
     * @DI\InjectParams({
     *     "annotationReader" = @DI\Inject("annotation_reader"),
     * })
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * {@inheritdoc}
     */
    public function read($resource)
    {
        $reflection = new \ReflectionClass($resource);

        return $this->getParamsFromClass($reflection);
    }

    public function getParamsFromClass(\ReflectionClass $class)
    {
        $annotation = $this->annotationReader->getClassAnnotation($class, $this->annotationName);

        return $annotation;
    }
}
