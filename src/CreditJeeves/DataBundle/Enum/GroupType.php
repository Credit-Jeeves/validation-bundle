<?php
namespace CreditJeeves\DataBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class GroupType extends Base
{
    const VEHICLE = 'vehicle';
    const ESTATE = 'estate';
    const GENERIC = 'generic';
    const RENT = 'rent';

    public static function getTypeList()
    {
        return array(
            self::ESTATE => ucwords(self::ESTATE),
                self::GENERIC => ucwords(self::GENERIC),
                self::RENT => ucwords(self::RENT),
                self::VEHICLE => ucwords(self::VEHICLE),
        );
    }
}
