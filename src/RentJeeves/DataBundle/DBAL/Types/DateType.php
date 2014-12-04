<?php
namespace RentJeeves\DataBundle\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateType as Base;
use RentJeeves\CoreBundle\DateTime;

class DateType extends Base
{
    const FIXED_DATE = 'fixed_date';

    const WRONG_DATE_STRING = '0000-00-00';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::FIXED_DATE;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }

        if ($value === self::WRONG_DATE_STRING) {
            return null;
        }

        if ($value instanceof \DateTime) {
            if ('DateTime' == get_class($value)) {
                return new DateTime($value->format('c'));
            }
            return $value;
        }

        $val = DateTime::createFromFormat('!'.$platform->getDateFormatString(), $value);
        if (!$val) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateFormatString()
            );
        }

        return new DateTime($val->format('c'));
    }
}
