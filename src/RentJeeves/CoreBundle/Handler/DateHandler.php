<?php

namespace RentJeeves\CoreBundle\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\DateHandler as BaseDateHandler;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\Exception\RuntimeException;

class DateHandler extends BaseDateHandler
{
    /**
     * @var string
     */
    protected $defaultFormat;

    /**
     * @var \DateTimeZone
     */
    protected $defaultTimezone;

    /**
     * @param string $defaultFormat
     * @param string $defaultTimezone
     * @param bool $xmlCData
     */
    public function __construct($defaultFormat = \DateTime::ISO8601, $defaultTimezone = 'UTC', $xmlCData = true)
    {
        $this->defaultFormat = $defaultFormat;
        $this->defaultTimezone = new \DateTimeZone($defaultTimezone);

        parent::__construct($defaultFormat, $defaultTimezone, $xmlCData);
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $types = ['DateTime', 'DateInterval'];

        foreach ($types as $type) {
            $methods[] = array(
                'type' => $type,
                'format' => 'array',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serialize'.$type,
            );
        }

        $methods[] = [
            'type' => 'DateTime',
            'format' => 'array',
            'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
            'method' => 'deserializeDateTimeFromArray',
        ];

        return $methods;
    }

    /**
     * @param VisitorInterface $visitor
     * @param mixed $data
     * @param array $type
     * @return \DateTime|null
     */
    public function deserializeDateTimeFromArray(VisitorInterface $visitor, $data, array $type)
    {
        if (empty($data)) {
            return null;
        }

        return $this->parseDateTime($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     * @return \DateTime
     */
    protected function parseDateTime($data, array $type)
    {
        $timezone = isset($type['params'][1]) ? new \DateTimeZone($type['params'][1]) : $this->defaultTimezone;
        $format = $this->getFormat($type);
        $datetime = \DateTime::createFromFormat($format, (string) $data, $timezone);
        if (false === $datetime) {
            throw new RuntimeException(sprintf('Invalid datetime "%s", expected format %s.', $data, $format));
        }

        return $datetime;
    }

    /**
     * @param array $type
     * @return string
     */
    protected function getFormat(array $type)
    {
        return isset($type['params'][0]) ? $type['params'][0] : $this->defaultFormat;
    }
}
