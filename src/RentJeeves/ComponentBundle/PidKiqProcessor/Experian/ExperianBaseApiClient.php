<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor\Experian;

use CreditJeeves\ExperianBundle\JMS\XmlSerializationVisitor;
use CreditJeeves\ExperianBundle\Model\NetConnectRequest;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\CurlException;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Monolog\Logger;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\InvalidArgumentException;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\InvalidResponseException;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\InvalidXmlException;

abstract class ExperianBaseApiClient
{
    const XML_SCHEMA = 'NCPreciseIDRequestV5_0';

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     * [
     *   "url" =>
     *   "dbHost" =>
     *   "subCode" =>
     *   "preciseIDEai" =>
     *   "preciseIDUserPwd" =>
     * ]
     */
    protected $config;

    /**
     * @var string
     */
    protected $lastResponse;

    /**
     * For additional headers
     * @var array
     */
    protected $headers = [];

    /**
     * Need just to check required elements of config
     * @var array
     */
    protected $configRequired = [
        'url',
        'dbHost',
        'subCode',
        'preciseIDEai',
        'preciseIDUserPwd',
        'rootPath',
    ];

    public function __construct(Logger $logger, EntityManager $em)
    {
        $this->logger = $logger;
        // dirty hack
        // TODO need change settings store to config.yml
        /** @var \CreditJeeves\DataBundle\Entity\Settings $settings */
        $settings = $em->getRepository('DataBundle:Settings')->find(1);
        $this->config['preciseIDUserPwd'] = $settings->getPreciseIDUserPwd();
        $this->config['preciseIDEai'] = $settings->getPreciseIDEai();
    }

    /**
     * @return string
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @return GuzzleClient
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new GuzzleClient('', ['redirect.disable' => true]);
        }

        return $this->client;
    }

    /**
     * @param array $config
     * @throws InvalidArgumentException
     */
    public function setConfig($config)
    {
        // dirty hack
        $config['preciseIDEai'] = $this->config['preciseIDEai'];
        $config['preciseIDUserPwd'] = $this->config['preciseIDUserPwd'];

        $presentKeys = array_intersect_key(array_flip($this->configRequired), $config);
        if (count($presentKeys) !== count($this->configRequired)) {
            throw new InvalidArgumentException('Missing required elements for config');
        }

        $this->config = $config;
    }

    /**
     * @param NetConnectRequest $request
     * @param null|string|array $serializationGroup
     * @return NetConnectResponse
     */
    protected function doRequest(NetConnectRequest $request, $serializationGroup = null)
    {
        $requestXml = $this->prepareRequest($request, $serializationGroup);

        $this->logger->debug(
            sprintf('Send Request to Experian: %s%s', "\n", $requestXml)
        );

        $responseXml = $this->__send($requestXml);

        $this->logger->debug(
            sprintf('Retrieve Response from Experian: %s%s', "\n", $responseXml)
        );

        return $this->prepareResponse($responseXml);
    }

    /**
     * @param string $request
     * @param string $method
     * @return string
     */
    protected function __send($request, $method = 'POST')
    {
        // Prepare request
        list($user, $pass) = explode(':', $this->config['preciseIDUserPwd']);
        $this->getClient()->setSslVerification();

        $guzzleRequest = $this->getClient()->createRequest(
            $method,
            $this->config['url'],
            $this->headers,
            $request
        );

        $guzzleRequest->setAuth($user, $pass);

        $guzzleResponse = $guzzleRequest->send();

        if (200 != $guzzleResponse->getStatusCode()) {
            throw new CurlException(sprintf('HTTP code not %d but %d', 200, $guzzleResponse->getStatusCode()));
        }

        $responseString = $guzzleResponse->getBody(true);

        return $responseString;
    }

    /**
     * Validate XML by provided schema name
     *
     * @param string $testXML
     * @param string $xmlSchema
     *
     * @throws InvalidXmlException
     */
    protected function validateXml($testXML, $xmlSchema)
    {
        libxml_use_internal_errors(true);
        $testDom = new \DOMDocument();
        $testDom->loadXML($testXML);

        $xmlSchema = $this->config['rootPath'] . "/../src/CreditJeeves/ExperianBundle/Resources/xsd/{$xmlSchema}.xsd";

        if (is_readable($xmlSchema) && !@$testDom->schemaValidate($xmlSchema)) {
            $exception = new InvalidXmlException(
                'Generated XML is invalid',
                E_PARSE
            );
            $exception->setWsdlErrors(libxml_get_errors());

            throw $exception;
        }
        libxml_use_internal_errors(false);
    }

    /**
     * @param NetConnectRequest $request
     * @param null|string|array $serializationGroup
     * @return string xml
     * @throws InvalidXmlException
     */
    protected function prepareRequest(NetConnectRequest $request, $serializationGroup = null)
    {
        $request
            ->setEai($this->config['preciseIDEai'])
            ->setDbHost($this->config['dbHost'])
            ->getRequest()->getProducts()->getPreciseIDServer()->getSubscriber()->setSubCode($this->config['subCode']);

        $xmlRequest = $this->getSerializer()->serialize(
            $request,
            'xml',
            $this->getSerializerContext($serializationGroup)
        );

        $this->validateXml($xmlRequest, static::XML_SCHEMA);

        return $xmlRequest;
    }

    protected function prepareResponse($xmlResponse)
    {
        $this->lastResponse = $xmlResponse;
        /**
         * @var NetConnectResponse $netConnectResponse
         */
        $netConnectResponse = $this->getSerializer()->deserialize(
            $xmlResponse,
            'CreditJeeves\ExperianBundle\Model\NetConnectResponse',
            'xml'
        );

        $this->validateResponse($netConnectResponse);

        return $netConnectResponse;
    }

    /**
     * @param null|string|array $group
     *
     * @return SerializationContext
     */
    protected function getSerializerContext($group = null)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        if ($group) {
            $context->setGroups($group);
        }

        return $context;
    }

    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        if (!$this->serializer) {
            $this->serializer = SerializerBuilder::create()
                ->setSerializationVisitor(
                    'xml',
                    new XmlSerializationVisitor(
                        new SerializedNameAnnotationStrategy(
                            new CamelCaseNamingStrategy('', false)
                        )
                    )
                )
                ->addDefaultDeserializationVisitors()
                ->build();
        }

        return $this->serializer;
    }

    /**
     * @param NetConnectResponse $netConnectResponse
     * @throws InvalidResponseException
     */
    abstract protected function validateResponse(NetConnectResponse $netConnectResponse);
}
