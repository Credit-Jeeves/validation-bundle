<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Services\SoapClientInterface;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Message;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\ExternalApiBundle\Soap\SoapClientBuilder;
use RentJeeves\ExternalApiBundle\Soap\SoapSettingsInterface;
use RentJeeves\ExternalApiBundle\Soap\SoapClient;
use Exception;
use RentJeeves\ExternalApiBundle\Soap\SoapWsdlTwigRenderer;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use \AppRjKernel as Kernel;

abstract class AbstractClient implements SoapClientInterface
{
    const MAPPING_DESERIALIZER_CLASS = 'class';

    const MAPPING_FIELD_STD_CLASS = 'field';

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * @var SoapClientBuilder
     */
    protected $soapClientBuilder;

    /**
     * @var YardiSettings
     */
    protected $settings;

    /**
     * @var SoapWsdlTwigRenderer
     */
    protected $wsdlRenderer;

    /**
     * @var string
     */
    protected $license;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Messages
     */
    protected $messages;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var boolean
     */
    protected $debug = false;

    /**
     * @var array
     */
    protected $mapping = array();

    /**
     * @var array
     */
    protected $errorMapping = array(
        "0"  => 'Could not open charge batch',
        "-1" => 'Web service user can insuffient rights',
        "-2" => 'Interface Entity does not have access to Yardi Property',
        "-3" => 'Interface Entity or License Error',
        "-4" => 'Login Failed',
    );

    /**
     * @var string
     */
    protected $pathToSoapClass;

    /**
     * @param SoapClient $soapClient
     */
    public function __construct(
        SoapWsdlTwigRenderer $wsdlRenderer,
        SoapClientBuilder $soapClientBuilder,
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer,
        Kernel $kernel,
        $entity,
        $license,
        $pathToSoapClasses
    ) {
        $this->soapClientBuilder = $soapClientBuilder;
        $this->entity = $entity;
        $this->wsdlRenderer = $wsdlRenderer;
        $this->license = $license;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->serializer = $serializer;
        $this->kernel = $kernel;
        $this->pathToSoapClass = $pathToSoapClasses;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    public function isDebugEnabled()
    {
        return $this->debug;
    }

    public function build()
    {
        $this->soapClientBuilder->setSettings($this->getSettings());
        $this->soapClientBuilder->setWsdlRenderer($this->wsdlRenderer);
        $this->soapClient = $this->soapClientBuilder->build();

        return $this;
    }

    /**
     * @param SoapSettingsInterface $settings
     */
    public function setSettings(SoapSettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return YardiSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    protected function getLoginCredentials()
    {
        return array(
            "UserName"          => $this->settings->getUsername(),
            "Password"          => $this->settings->getPassword(),
            "ServerName"        => $this->settings->getDatabaseServer(),
            "Database"          => $this->settings->getDatabaseName(),
            "Platform"          => $this->settings->getPlatform(),
            "InterfaceEntity"   => $this->entity,
            "InterfaceLicense"  => $this->getLicense(),
        );
    }

    /**
     * @return string
     */
    protected function getLicense()
    {
        $path = $this->kernel->locateResource(
            '@ExternalApiBundle/Resources/files/'.$this->license
        );
        return file_get_contents($path);
    }

    /**
     * @param string|integer $response
     * @return bool
     */
    public function isNumericError($response)
    {
        $this->debugMessage($response);
        settype($response, 'string');

        if (isset($this->errorMapping[$response])) {
            $this->setErrorMessage($this->errorMapping[$response]);
            return true;
        }

        return false;
    }

    /**
     * @param string $response
     *
     * @return bool
     */
    public function isXmlError($response)
    {
        $this->messages = $this->serializer->deserialize(
            $response,
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages',
            'xml'
        );
        if (empty($this->messages)) {
            return false;
        }
        $this->debugMessage($this->messages);
        /**
         * @var $message Message
         */
        $message = $this->messages->getMessage();
        if (empty($message)) {
            return false;
        }

        if (in_array($message->getMessageType(), array('Error', 'Warning'))) {
            return true;
        };
        $this->messages = null;

        return false;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        if ($this->messages) {
            return true;
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        if ($this->messages) {
            return $this->messages->getMessage()->getMessage();
        }

        return null;
    }

    /**
     * @param string $messageString
     */
    public function setErrorMessage($messageString)
    {
        $this->debugMessage($messageString);
        $messages = new Messages();
        $message = new Message();
        $message->setMessage($messageString);
        $messages->setMessage($message);
        $this->messages = $messages;
    }

    /**
     * @param string $function
     * @param array $params
     *
     * @return mixed
     */
    protected function processRequest($function, array $params)
    {
        try {
            $this->messages = null;
            if (!isset($this->mapping[$function])) {
                throw new Exception(
                    sprintf(
                        "Don't have mapping for function: %s",
                        $function
                    )
                );
            }

            $response = $this->soapClient->__soapCall($function, $params);
            $resultXmlResponse = $this->processXmlResponse($response, $function);

            if ($resultXmlResponse) {
                return $resultXmlResponse;
            }

            if ($this->isError()) {
                return null;
            }

            $resultNumericResponse = $this->processNumericResponse($response, $function);
            if ($resultNumericResponse) {
                return $resultNumericResponse;
            }

            if ($this->isError()) {
                return null;
            }

            throw new Exception(
                sprintf(
                    "Bad Response: %s",
                    $this->soapClient->__getLastResponse()
                )
            );
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->setErrorMessage($e->getMessage());
            $this->debugMessage($e->getMessage());
        }

        return null;
    }

    /**
     * @param $responce
     * @param $function
     *
     * @return null|integer
     */
    protected function processNumericResponse($responce, $function)
    {
        $responseField = $this->mapping[$function][self::MAPPING_FIELD_STD_CLASS];
        if (isset($responce->$responseField) && !$this->isNumericError($responce->$responseField)) {
            return $responce->$responseField;
        }

        return null;
    }

    /**
     * @param $response
     * @param $function
     *
     * @return mixed|null|string
     */
    protected function processXmlResponse($response, $function)
    {
        $responseField = $this->mapping[$function][self::MAPPING_FIELD_STD_CLASS];
        $deserializeClass = $this->mapping[$function][self::MAPPING_DESERIALIZER_CLASS];

        $this->debugMessage($response);
        if (!isset($response->$responseField->any) || empty($deserializeClass)) {
            return null;
        }

        $xml = $response->$responseField->any;
        $xml = $this->getXmlHeader().$xml;

        if ($this->isXmlError($xml)) {
            return null;
        }

        $deserializeClass = $this->pathToSoapClass.$deserializeClass;

        return $this->serializer->deserialize(
            $xml,
            $deserializeClass,
            'xml'
        );
    }

    /**
     * @return string
     */
    protected function getXmlHeader()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>';
    }

    /**
     * @param $var
     */
    protected function debugMessage($var)
    {
        if (!$this->isDebugEnabled()) {
            return;
        }
        echo "\n";
        var_dump($var);
        echo "\n";
    }

    public function getFullResponse($show = true)
    {
        return $this->getSoapData('__getLastResponse', $show);
    }

    public function getFullRequest($show = true)
    {
        return $this->getSoapData('__getLastRequest', $show);
    }

    protected function getSoapData($method, $show)
    {
        $methodHeader = $method.'Headers';
        $request = array(
            'header' => $this->soapClient->$methodHeader(),
            'body'   => $this->soapClient->$method()
        );

        if ($show) {
            print_r($request);
        }

        return $request;
    }
}
