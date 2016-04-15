<?php
namespace RentJeeves\ComponentBundle\Utility;

class ShorteningAddressUtility extends ShorteningUtility
{
    const MAX_LENGTH = 50;

    protected static $vocabulary = [
        'East' => 'E',
        'West' => 'W',
        'North' => 'N',
        'South' => 'S',
        'Southwest' => 'SW',
        'Southeast' => 'SE',
        'Northwest' => 'NW',
        'Northeast' => 'NE',

        'Street' => 'St',
        'Avenue' => 'Ave',
        'Boulevard' => 'Blvd',
        'Highway' => 'Hwy',
        'Parkway' => 'Pkwy',
    ];

    /**
     * @param string $address
     * @param int $length
     * @param array $shrinkVocabulary
     * @return string
     */
    public static function shrinkAddress($address, $length = self::MAX_LENGTH, array $shrinkVocabulary = null)
    {
        $shrinkVocabulary || $shrinkVocabulary = static::$vocabulary;

        if (mb_strlen($address) > $length) {
            $address = static::replaceByVocabulary($address, $shrinkVocabulary);
            $address = static::shrink($address, $length);
        }

        return $address;
    }
}
