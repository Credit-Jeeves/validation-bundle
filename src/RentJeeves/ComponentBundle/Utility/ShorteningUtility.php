<?php
namespace RentJeeves\ComponentBundle\Utility;

class ShorteningUtility
{
    const MAX_LENGTH = 255;

    /**
     * @param $string
     * @param int $length
     * @return string
     */
    public static function shrink($string, $length = self::MAX_LENGTH)
    {
        $string = trim($string);

        if (mb_strlen($string) > $length) {
            $half = (int) ($length / 2);
            $firstPart = substr($string, 0, $half);

            $lastPart = substr($string, -$half);
            $string = $firstPart . $lastPart;
        }

        return $string;
    }

    /**
     * @param $string
     * @param $vocabulary
     * @return mixed
     */
    public static function replaceByVocabulary($string, $vocabulary)
    {
        $pattern = array_map(
            function ($value) {
                return '/\b'. $value . '\b/';
            },
            array_keys($vocabulary)
        );
        $replacement = array_values($vocabulary);

        return preg_replace($pattern, $replacement, $string);
    }
}
