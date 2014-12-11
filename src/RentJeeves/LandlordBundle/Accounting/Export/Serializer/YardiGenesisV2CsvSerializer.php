<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as Serializer;

class YardiGenesisV2CsvSerializer extends YardiGenesisCsvSerializer
{
    public function serialize($data)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('YardiGenesisV2');
        $context->setAttribute('use_header', false);
        $context->setAttribute('eol', "\r");
        $content = $this->serializer->serialize($data, 'csv', $context);

        return $content;
    }
}
