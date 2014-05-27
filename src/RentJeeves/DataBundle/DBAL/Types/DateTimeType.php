<?php
namespace RentJeeves\DataBundle\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType as Base;
use RentJeeves\CoreBundle\DateTime;

class DateTimeType extends Base
{
    const FIXED_DATETIME = 'fixed_datetime';

    public function getName()
    {
        return static::FIXED_DATETIME;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }
        if ($value instanceof \DateTime) {
            if ('DateTime' == get_class($value)) {
                return new DateTime($value->format('c'));
            }
            return $value;
        }

        $val = DateTime::createFromFormat($platform->getDateTimeFormatString(), $value);
        if (!$val) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateTimeFormatString()
            );
        }

        return new DateTime($val->format('c'));
    }
}
