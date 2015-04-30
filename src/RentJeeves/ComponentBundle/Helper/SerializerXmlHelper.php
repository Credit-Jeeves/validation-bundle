<?php

namespace RentJeeves\ComponentBundle\Helper;

use JMS\Serializer\SerializationContext;

class SerializerXmlHelper
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
    public static function getSerializerContext(array $groups, $serializeNull = true)
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

    /**
     * @param string $string
     * @return string
     */
    public static function addCDataToString($string)
    {
        return sprintf("<![CDATA[%s]]>", $string);
    }

    /**
     * @param string $tag
     * @param string $namespace
     * @param string $string
     * @return string
     */
    public static function addTagWithNameSpaceToString($tag, $namespace, $string)
    {
        return sprintf(
            "<%s:%s>%s</%s:%s>",
            $namespace,
            $tag,
            $string,
            $namespace,
            $tag
        );
    }

    /**
     * @param string $string
     * @return string
     */
    public static function replaceEscapeToCorrectSymbol($string)
    {
        return str_replace(['&lt;', '&gt;'], ['<', '>'], $string);
    }
}
