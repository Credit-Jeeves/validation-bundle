<?php
namespace RentJeeves\DataBundle\Utility;

class ShorteningAddressUtility
{
    const MAX_LENGTH = 50;

    const BEGIN_PATTERN = '(^|\s+)';

    const END_PATTERN = '([$\s+,.])';


    public static function truncate($string)
    {
        if (mb_strlen($string) > static::MAX_LENGTH) {
            $half = (int) (static::MAX_LENGTH / 2);
            $firstPart = substr($string, 0, $half);

            $lastPart = substr($string, -$half);
            $string = $firstPart . $lastPart;
        }

        return $string;
    }

    protected static $vocabulary = [
        'East' => 'E',
        'West' => 'W',
        'North' => 'N',
        'South' => 'S',

        'Street' => 'St',
        'Avenue' => 'Ave',
        'Boulevard' => 'Blvd',
    ];
    public static function truncateAddress($address)
    {
        if (mb_strlen($address) > static::MAX_LENGTH) {
            $address = static::replaceByVocabulary($address, static::$vocabulary);
            $address = static::truncate($address);
        }

        return $address;
    }

    public static function replaceByVocabulary($string, $vocabulary)
    {
        $begin = static::BEGIN_PATTERN;
        $end = static::END_PATTERN;

        $pattern = array_map(
            function($value) use ($begin, $end) {
                return '/' . $begin . $value . $end . '/';
            },
            array_keys($vocabulary)
        );
        $replacement = array_map(
            function($value) {
                return '$1' . $value . '$2';
            },
            array_values($vocabulary)
        );

        return preg_replace($pattern, $replacement, $string);
    }
}
