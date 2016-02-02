<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as Serializer;
use RentJeeves\LandlordBundle\Model\MRIBostonPostExport;

/**
 * export.serializer.mri_boston_post
 */
class MRIBostonPostCsvSerializer implements ExportSerializerInterface
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
        $context->setGroups('mri_boston_post');
        $context->setAttribute('use_header', false);
        $mappedData = [];
        /** @var Order $order */
        foreach ($data as $order) {
            $bostonExport = new MRIBostonPostExport($order);
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
