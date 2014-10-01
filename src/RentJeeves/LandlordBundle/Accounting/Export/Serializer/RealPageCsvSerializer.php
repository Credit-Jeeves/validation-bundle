<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\Serializer\SerializerInterface as Serializer;

/**
 * @Service("export.serializer.real_page")
 */
class RealPageCsvSerializer implements ExportSerializerInterface
{
    protected $serializer;

    /**
     * @InjectParams({
     *     "serializer" = @Inject("jms_serializer")
     * })
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize($data)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('realPageReport');
        $context->setAttribute('use_header', false);
        $content = $this->serializer->serialize($data, 'csv', $context);

        return $content;
    }

    public function getContentType()
    {
        return 'text/csv';
    }
}
