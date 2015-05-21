<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor\Exception;

class PidKiqInvalidXmlException extends \Exception
{
    /**
     * @var array
     */
    protected $wsdlErrors = [];

    /**
     * @param array $errors
     */
    public function setWsdlErrors(array $errors)
    {
        $this->wsdlErrors = $errors;
    }

    /**
     * @return array<\LibXMLError>
     */
    public function getWsdlErrors()
    {
        return $this->wsdlErrors;
    }
}
