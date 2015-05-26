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
        !count($errors) || $this->message .= sprintf('Errors: %s', $this->getWsdlErrorsMessages());
    }

    /**
     * @return array<\LibXMLError>
     */
    public function getWsdlErrors()
    {
        return $this->wsdlErrors;
    }

    /**
     * @param bool $asString
     * @param string $separator
     * @return array <string>|string
     */
    public function getWsdlErrorsMessages($asString = true, $separator = '')
    {
        $messages = [];
        foreach ($this->getWsdlErrors() as $error) {
            /** @var \LibXMLError $error */
            $messages[] = trim($error->message);
        }

        return $asString ? implode($separator, $messages) : $messages;
    }
}
