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
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\PidKiqInvalidArgumentException;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\PidKiqInvalidResponseException;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\PidKiqInvalidXmlException;

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
     * @return GuzzleClient
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new GuzzleClient('', ['redirect.disable' => true]);
            $this->client->setSslVerification();
        }

        return $this->client;
    }

    /**
     * @param array $config
     * @throws PidKiqInvalidArgumentException
     */
    public function setConfig($config)
    {
        // dirty hack
        // TODO Fix this
        $config['preciseIDEai'] = $this->config['preciseIDEai'];
        $config['preciseIDUserPwd'] = $this->config['preciseIDUserPwd'];

        $presentKeys = array_intersect_key(array_flip($this->configRequired), $config);
        if (count($presentKeys) !== count($this->configRequired)) {
            throw new PidKiqInvalidArgumentException('Missing required parameters for experian config');
        }

        $this->config = $config;
    }

    /**
     * @param NetConnectRequest $request
     * @param null|string|array $serializationGroup
     * @return NetConnectResponse
     * @throws \Exception
     */
    protected function doRequest(NetConnectRequest $request, $serializationGroup = null)
    {
        $requestXml = $this->prepareRequest($request, $serializationGroup);

        $this->logger->debug(
            sprintf('[Experian]Send Request: %s%s', "\n", $requestXml)
        );

        try {
            $responseXml = $this->__send($requestXml);
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf('[Experian]Get error when try to send request: %s', $e->getMessage())
            );
            throw $e;
        }

        $this->logger->debug(
            sprintf('[Experian]Retrieve Response: %s%s', "\n", $responseXml)
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
        $guzzleRequest = $this->getClient()->createRequest(
            $method,
            $this->config['url'],
            $this->headers,
            $request
        );

        list($user, $pass) = explode(':', $this->config['preciseIDUserPwd']);
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
     * @throws PidKiqInvalidXmlException
     */
    protected function validateXml($testXML, $xmlSchema)
    {
        libxml_use_internal_errors(true);
        $testDom = new \DOMDocument();
        $testDom->loadXML($testXML);

        $xmlSchema = __DIR__ . "/../../Resources/xsd/{$xmlSchema}.xsd";

        if (is_readable($xmlSchema) && !@$testDom->schemaValidate($xmlSchema)) {
            $exception = new PidKiqInvalidXmlException(
                'Generated XML is invalid',
                E_PARSE
            );
            $exception->setWsdlErrors(libxml_get_errors());

            $this->logger->alert('[Experian]Get Validation Error on Request', $exception->getWsdlErrors());

            throw $exception;
        }
        libxml_use_internal_errors(false);
    }

    /**
     * @param NetConnectRequest $request
     * @param null|string|array $serializationGroup
     * @return string xml
     * @throws PidKiqInvalidXmlException
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
            $this->getSerializationContext($serializationGroup)
        );

        $this->logger->debug(
            sprintf('[Experian]Prepared request:%s%s', "\n", $xmlRequest)
        );

        $this->validateXml($xmlRequest, static::XML_SCHEMA);

        return $xmlRequest;
    }

    protected function prepareResponse($xmlResponse)
    {
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
    protected function getSerializationContext($group = null)
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
     * @throws PidKiqInvalidResponseException
     */
    abstract protected function validateResponse(NetConnectResponse $netConnectResponse);
}
