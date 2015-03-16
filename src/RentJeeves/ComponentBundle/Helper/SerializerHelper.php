<?php

namespace RentJeeves\ComponentBundle\Helper;

use JMS\Serializer\SerializationContext;

class SerializerHelper
{
    /**
     * @param string $xml
     * @return string
     */
    public static function removeStandartHeaderXml($xml)
    {
        return str_replace(
            [self::getStandartXmlHeader()],
            '',
            $xml
        );
    }

    /**
     * @param array $groups
     * @param bool $serializeNull
     * @return SerializationContext
     */
    public static function getSerializerContext($groups = [], $serializeNull = true)
    {
        $context = new SerializationContext();
        $context->setGroups($groups);
        $context->setSerializeNull($serializeNull);

        return $context;
    }

    /**
     * @return string
     */
    public static function getStandartXmlHeader()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>';
    }
}
