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
     * @param string $tenantName
     * @return array|null
     */
    public static function extractFirstAndLastName($tenantName)
    {
        if (empty(trim($tenantName))) {
            return null;
        }
        //Remove initial
        $tenantName = preg_replace('/[A-Za-z]{0,6}\\.\\s*/', '', $tenantName);
        //Remove all non-alpha or spaces + &
        $tenantName = preg_replace('/[^a-zA-Z\\s&]/', '', $tenantName);
        $isUsedAmpersand =  false;
        if (preg_match('/&/', $tenantName)) {
            $tenantName = str_replace('&', ' ', $tenantName);
            $isUsedAmpersand = true;
        }
        if (preg_match('/ and /', $tenantName)) {
            $tenantName = str_replace(' and ', ' ', $tenantName);
            $isUsedAmpersand = true;
        }
        $tenantName = explode(' ', $tenantName);
        //reindex array and remove empty element
        $tenantName = array_values(array_filter($tenantName));
        //Step4: Use first person from duplicate people with same last name: ("Bob & Damian Marley" => "Bob Marley")
        if (count($tenantName) === 3) {
            return [
                'firstName' => $tenantName[0],
                'lastName' => $tenantName[2],
            ];
        //Step5: Use first and last words if we had to use ampersand
        } elseif (count($tenantName) > 2 && $isUsedAmpersand === false) {
            return [
                'firstName' => $tenantName[0],
                'lastName'  => end($tenantName),
            ];
        }
        //Step6: Use first person from duplicate people with different last name
        return [
            'firstName'  => $tenantName[0],
            'lastName'  => $tenantName[1],
        ];
    }
}

