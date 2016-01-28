<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Response\Exception\ResponseResourceException;
use RentJeeves\ApiBundle\Services\Encoders\AttributeEncoderInterface;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\ResourceUrlGenerator;

abstract class ResponseResource
{
    /**
     * @var ResourceUrlGenerator
     * @DI\Inject("api.resource_url_generator", required=true)
     */
    public $urlGenerator;

    /**
     * @var AttributeEncoderInterface
     * @DI\Inject("api.default_id_encoder", required=true)
     */
    public $encoder;

    /**
     * @var ResponseFactory
     * @DI\Inject("response_resource.factory", required=true)
     */
    public $resourceFactory;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Base"})
     * @Serializer\Type("integer")
     * @return int
     */
    public function getId()
    {
        return $this->encoder->encode($this->entity->getId());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Base"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getUrl()
    {
        return $this->urlGenerator->generate($this);
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        throw new ResponseResourceException(
            sprintf('Property "%s.%s" is not defined.', get_class($this), $name)
        );
    }

    public function __toString()
    {
        return $this->getUrl();
    }
}
