<?php

namespace RentJeeves\CoreBundle\Helpers;

class HashHeaderCreator
{
    /**
     * @param array  $arrayHeaders
     * @param string $delimiter
     *
     * @return string
     */
    public static function createHashHeader(array $arrayHeaders, $delimiter = '')
    {
        return md5(implode($delimiter, array_values($arrayHeaders)));
    }
}
