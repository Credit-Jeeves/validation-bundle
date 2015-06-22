<?php

namespace RentJeeves\ApiBundle\Services\Encoders;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("resource.url_encoder")
 */
class ResourceUrlEncoder extends Encoder
{
    /**
     * @var AttributeEncoderInterface
     */
    protected $idEncoder;

    /**
     * @var EncoderFactory
     */
    protected $encoderFactory;

    /**
     * @param EncoderFactory $encoderFactory
     * @param AttributeEncoderInterface $idEncoder
     *
     * @DI\InjectParams({
     *     "encoderFactory" = @DI\Inject("encoder_factory"),
     *     "idEncoder"      = @DI\Inject("api.default_id_encoder")
     * })
     */
    public function __construct(EncoderFactory $encoderFactory, AttributeEncoderInterface $idEncoder)
    {
        $this->encoderFactory = $encoderFactory;
        $this->idEncoder = $idEncoder;
    }

    /**
     * @param $idEncoderConfig
     * @return self
     * @throws EncoderException
     */
    public function setIdEncoder($idEncoderConfig)
    {
        if ($this->idEncoder = $this->encoderFactory->getEncoder($idEncoderConfig)) {
            return $this;
        }

        throw new EncoderException("Invalid Params for Id Encoder");
    }

    public function decode($url)
    {
        $encodedId =  preg_filter('/(.*)\/(\d+)/', '$2', $url);

        if ($this->isValidForDecryption($encodedId) || $this->skipNotValid) {
            return $this->idEncoder->decode($encodedId);
        }

        throw new ValidationEncoderException(
            sprintf('Invalid url resource "%s" for decoding.', $url)
        );
    }

    public function isValidForDecryption($encodedId)
    {
        if (!empty($encodedId)) {
            return true;
        }

        return false;
    }
}
