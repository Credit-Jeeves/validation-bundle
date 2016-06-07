<?php

namespace RentJeeves\ImportBundle\Helper;

class ShortNameFetcher
{
    /**
     * @param string $fullName
     * @return string|null
     */
    public static function extractFirstName($fullName)
    {
        $data = self::extractFirstAndLastName($fullName);
        if (!empty($data) && array_key_exists('firstName', $data)) {
            return $data['firstName'];
        }

        return null;
    }

    /**
     * @param string $fullName
     * @return string|null
     */
    public static function extractLastName($fullName)
    {
        $data = self::extractFirstAndLastName($fullName);

        if (!empty($data) && array_key_exists('lastName', $data)) {
            return $data['lastName'];
        }

        return null;
    }

    /**
     * @param string $fullName
     * @return array|null
     */
    public static function extractFirstAndLastName($fullName)
    {
        if (empty(trim($fullName))) {
            return null;
        }
        //Remove initial
        $fullName = preg_replace('/[A-Za-z]{0,6}\\.\\s*/', '', $fullName);
        //Remove all non-alpha or spaces + &
        $fullName = preg_replace('/[^a-zA-Z\\s&]/', '', $fullName);
        $isUsedAmpersand =  false;
        if (preg_match('/&/', $fullName)) {
            $fullName = str_replace('&', ' ', $fullName);
            $isUsedAmpersand = true;
        }
        if (preg_match('/ and /', $fullName)) {
            $fullName = str_replace(' and ', ' ', $fullName);
            $isUsedAmpersand = true;
        }
        $fullName = explode(' ', $fullName);
        //reindex array and remove empty element
        $fullName = array_values(array_filter($fullName));
        //Step4: Use first person from duplicate people with same last name: ("Bob & Damian Marley" => "Bob Marley")
        if (count($fullName) === 3) {
            return [
                'firstName' => $fullName[0],
                'lastName' => $fullName[2],
            ];
        //Step5: Use first and last words if we had to use ampersand
        } elseif (count($fullName) > 2 && $isUsedAmpersand === false) {
            return [
                'firstName' => $fullName[0],
                'lastName'  => end($fullName),
            ];
        }
        //Step6: Use first person from duplicate people with different last name
        return [
            'firstName'  => $fullName[0],
            'lastName'  => $fullName[1],
        ];
    }
}
