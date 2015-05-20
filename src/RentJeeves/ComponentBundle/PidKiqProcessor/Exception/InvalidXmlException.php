<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor\Exception;

class InvalidXmlException extends \Exception
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

//    /**
//     * @return null|string
//     */
//    public function getErrorWsdl()
//    {
//        if (count($this->wsdlErrors) % 2 === 0 && isset($this->wsdlErrors[1])) {
//            /**
//             * @var $libXmlError \LibXMLError
//             */
//            $libXmlError = $this->wsdlErrors[1];
//            return $libXmlError->message;
//        }
//
//        return null;
//    }
}
