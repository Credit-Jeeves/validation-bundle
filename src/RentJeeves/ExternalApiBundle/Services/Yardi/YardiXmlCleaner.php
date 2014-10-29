<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use DOMDocument;

class YardiXmlCleaner
{
    public static function prepareXml($xml)
    {
        if (empty($xml)) {
            return $xml;
        }

        $domXml = new DOMDocument();
        $domXml->loadXML($xml);
        $xmlOut = $domXml->saveXML($domXml->documentElement);
        $xmlOut = '<ns1:TransactionXml>' . $xmlOut;
        $xmlOut = $xmlOut . '</ns1:TransactionXml>';
        $xmlOut = str_replace(' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xmlOut);

        return $xmlOut;
    }
}
