<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as Serializer;
use RentJeeves\LandlordBundle\Model\BostonPostExport;

/**
 * export.serializer.boston_post
 */
class BostonPostCsvSerializer implements ExportSerializerInterface
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $data
     * @return string
     */
    public function serialize($data)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('boston');
        $context->setAttribute('use_header', false);
        $mappedData = [];
        /** @var Order $order */
        foreach ($data as $order) {
            $bostonExport = new BostonPostExport($order);
            $mappedData[] = $bostonExport;
        }

        return $this->serializer->serialize($mappedData, 'csv', $context);
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return 'text/csv';
    }
}
