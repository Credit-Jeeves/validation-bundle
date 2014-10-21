<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\Encoders\AttributeEncoderInterface;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\ResourceUrlGenerator;

abstract class ResponseResource
{
    /**
     * @var ResourceUrlGenerator
     * @DI\Inject("api.resource_url_generator")
     */
    public $urlGenerator;

    /**
     * @var AttributeEncoderInterface
     * @DI\Inject("api.default_id_encoder")
     */
    public $encoder;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Base"})
     *
     * @return int
     */
    public function getId()
    {
        return $this->encoder->encode($this->entity->getId());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Base"})
     *
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
}
