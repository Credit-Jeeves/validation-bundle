<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait as Debug;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait as Settings;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Message;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\ExternalApiBundle\Soap\SoapClientBuilder;
use RentJeeves\ExternalApiBundle\Soap\SoapClient;
use Exception;
use RentJeeves\ExternalApiBundle\Soap\SoapWsdlTwigRenderer;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use \AppRjKernel as Kernel;
use SoapFault;
use Symfony\Bridge\Monolog\Logger;

abstract class AbstractClient implements ClientInterface
{
    use Debug;
    use Settings;

    const MAPPING_DESERIALIZER_CLASS = 'class';

    const MAPPING_FIELD_STD_CLASS = 'field';

    const MAX_NUMBER_OF_RETRIES = 2;

    //seconds
    const SLEEP_BETWEEN_RETRIES = 2;

    const DEFAULT_NUMBER_OF_RETRIES = 0;

    protected $numberOfRetriesTheSameSoapCall = self::DEFAULT_NUMBER_OF_RETRIES;
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
     * @var Logger
     */
    protected $logger;

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
        Logger $logger,
        SoapWsdlTwigRenderer $wsdlRenderer,
        SoapClientBuilder $soapClientBuilder,
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer,
        Kernel $kernel,
        $entity,
        $license,
        $pathToSoapClasses
    ) {
        $this->logger = $logger;
        $this->soapClientBuilder = $soapClientBuilder;
        $this->entity = $entity;
        $this->wsdlRenderer = $wsdlRenderer;
        $this->license = $license;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->serializer = $serializer;
        $this->kernel = $kernel;
        $this->pathToSoapClass = $pathToSoapClasses;
    }

    public function build()
    {
        $this->soapClientBuilder->setSettings($this->getSettings());
        $this->soapClientBuilder->setWsdlRenderer($this->wsdlRenderer);
        $this->soapClient = $this->soapClientBuilder->build();

        return $this;
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
    public function sendRequest($function, array $params)
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
                $this->numberOfRetriesTheSameSoapCall = self::DEFAULT_NUMBER_OF_RETRIES;
                return $resultXmlResponse;
            }

            if ($this->isError()) {
                $this->numberOfRetriesTheSameSoapCall = self::DEFAULT_NUMBER_OF_RETRIES;
                return null;
            }

            $resultNumericResponse = $this->processNumericResponse($response, $function);
            if ($resultNumericResponse) {
                $this->numberOfRetriesTheSameSoapCall = self::DEFAULT_NUMBER_OF_RETRIES;
                return $resultNumericResponse;
            }

            if ($this->isError()) {
                $this->numberOfRetriesTheSameSoapCall = self::DEFAULT_NUMBER_OF_RETRIES;
                return null;
            }

            throw new Exception(
                sprintf(
                    "Bad Response: %s",
                    $this->soapClient->__getLastResponse()
                )
            );
        } catch (SoapFault $e) {
            if ($this->numberOfRetriesTheSameSoapCall > self::MAX_NUMBER_OF_RETRIES) {
                throw $e;
            }
            $this->numberOfRetriesTheSameSoapCall++;
            $this->logger->addWarning(
                sprintf(
                    "Yardi send request was failed with message: %s. We try again send request. Number of retries: %s",
                    $e->getMessage(),
                    $this->numberOfRetriesTheSameSoapCall
                )
            );
            sleep(self::SLEEP_BETWEEN_RETRIES);

            return $this->sendRequest($function, $params);
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->setErrorMessage($e->getMessage());
            $this->debugMessage($e->getMessage());
        }
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
