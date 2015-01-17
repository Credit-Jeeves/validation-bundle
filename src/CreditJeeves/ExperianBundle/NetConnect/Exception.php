<?php
namespace CreditJeeves\ExperianBundle\NetConnect;

use Exception as Base;

class Exception extends Base
{
    protected $wsdlErrors = [];

    public function setWsdlErrors($errors = array())
    {
        $this->wsdlErrors = $errors;
    }

    public function getWsdlErrors()
    {
        return $this->wsdlErrors;
    }

    public function getErrorWsdl()
    {
        if (count($this->wsdlErrors) % 2 === 0 && isset($this->wsdlErrors[1])) {
            /**
             * @var $libXmlError \LibXMLError
             */
            $libXmlError = $this->wsdlErrors[1];
            return $libXmlError->message;
        }

        return null;
    }
}
