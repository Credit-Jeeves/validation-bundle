<?php
namespace CreditJeeves\ExperianBundle;

use CreditJeeves\DataBundle\Entity\Settings;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\ExperianBundle\JMS\XmlSerializationVisitor;
use CreditJeeves\ExperianBundle\Model\NetConnectRequest;
use CreditJeeves\ExperianBundle\Model\PrimaryApplicant;
use CreditJeeves\ExperianBundle\NetConnect\Exception;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Serializer;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\BadResponseException;
use DOMDocument;

/**
 * Base class
 * DI\Service("experian.net_connect") It is defined in services.yml
 */
abstract class NetConnect
{
    /**
     * @var array
     */
    protected $headers = array();
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $usrPwd;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var string
     */
    protected $serverName;

    /**
     * @var bool
     */
    protected $isLogging = false;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var NetConnectRequest
     */
    protected $netConnectRequest;

    /**
     * @var string
     */
    protected $logPath;

    /**
     * @var string
     */
    protected $uploadDir;

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * DI\InjectParams({ It is defined in services.yml
     *     "em"         = DI\Inject("@doctrine.orm.default_entity_manager"),
     *     "isLogging"  = DI\Inject("%experian.logging%"),
     *     "serverName" = DI\Inject("%server_name%"),
     *     "logPath"    = DI\Inject("%kernel.logs_dir%"),
     *     "uploadDir"  = DI\Inject("%web.upload.dir%")
     * })
     *
     * @param EntityManager $em
     * @param bool $isLogging
     * @param string $serverName
     * @param string $logPath
     * @param string $uploadDir
     */
    public function __construct(EntityManager $em, $isLogging, $serverName, $logPath, $uploadDir)
    {
        $this->settings = $em->getRepository('DataBundle:Settings')->find(1);;
        $this->isLogging = $isLogging;
        $this->serverName = $serverName;
        $this->logPath = $logPath;
        $this->uploadDir = $uploadDir;
    }

    /**
     * @return GuzzleClient
     */
    protected function getClient()
    {
        if (null == $this->client) {
            $this->client = new GuzzleClient('', array('redirect.disable' => true));
        }
        return $this->client;
    }

    /**
     * @param string $url
     * @param string $dbHost
     * @param string $subCode
     *
     * @return $this
     */
    abstract public function setConfigs($url, $dbHost, $subCode);

    /**
     * @return NetConnectRequest
     */
    public function getNetConnectRequest()
    {
        if (null == $this->netConnectRequest) {
            $this->netConnectRequest = new NetConnectRequest();
        }
        return $this->netConnectRequest;
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
        if (null == $this->serializer) {
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

    protected function addUserToRequest(User $user, PrimaryApplicant $model)
    {
        $model->getName()
            ->setFirst($user->getFirstName())
            ->setSurname($user->getLastName())
            ->setMiddle($user->getMiddleInitial());
        $model->setSsn($user->getSsn());
        $model->getPhone()
            ->setNumber($user->getPhone());
//            ->setType($user->getPhoneType()); //TODO find out format
        if ($defaultAddress = $user->getDefaultAddress()) {
            $model->getCurrentAddress()
                ->setCity($defaultAddress->getCity())
                ->setState($defaultAddress->getArea())
                ->setStreet($defaultAddress->getStreet())
                ->setZip($defaultAddress->getZip());
        }
        $model->setDob($user->getDBO());
//        $model->setEmailAddress($user->getEmail()); //TODO do we need to sed it?
    }

    /**
     * @param string $date
     * @param string $postfix
     */
    protected function log($date, $postfix = '')
    {
        if ($this->isLogging) {
            file_put_contents(
                sprintf("%s/experian/%s%s.xml", $this->logPath, str_replace('\\', '-', get_class($this)), $postfix),
                $date
            );
        }
    }

    /**
     * @param string $request
     * @param string $logPrefix
     * @param string $method
     *
     * @return string
     */
    protected function doRequest($request, $logPrefix = '', $method = 'POST')
    {
        $this->log($request, $logPrefix . '-Request');
        $usrPwdArr = explode(':', $this->usrPwd);
        $this->getClient()->setSslVerification();
        $guzzleRequest = $this->getClient()->createRequest(
            $method,
            $this->url,
            $this->headers,
            $request
        );
        $guzzleRequest->getParams()->set('redirect.disable', true);
        $guzzleRequest->setAuth($usrPwdArr[0], $usrPwdArr[1]);


        // Let BrowserKit handle redirects
        try {
            $response = $guzzleRequest->send();
        } catch (CurlException $e) {
            if (!strpos($e->getMessage(), 'redirects')) {
                throw $e;
            }

            $response = $e->getResponse();
        }
        if (200 != $response->getStatusCode()) {
            throw new CurlException(sprintf('HTTP code not %d but %d', 200, $response->getStatusCode()));
        }
        $responseString = $response->getBody(true);
        $this->log($responseString, $logPrefix . '-Response');
        return $responseString;
    }

    /**
     * Validate XML by provided schema name
     *
     * @param string $testXML
     * @param string $xmlSchema
     *
     * @throws Exception
     */
    public function validate($testXML, $xmlSchema)
    {
        if (!$this->uploadDir || !$this->serverName) {
            return;
        }
        libxml_use_internal_errors(true);
        $testDom = new DOMDocument();
        $testDom->loadXML($testXML);

        $xmlSchema = __DIR__ . "/Resources/xsd/{$xmlSchema}.xsd";
        if (is_readable($xmlSchema) && !@$testDom->schemaValidate($xmlSchema)) {
            $errors = print_r(libxml_get_errors(), true);
            $name = '/logs/xml/' . date('Y-m-d_His') . '_' . md5(rand(0, 9999999999999)) . '.xml';
            file_put_contents(
                $this->uploadDir . $name,
                $testXML . "<!--{$errors}-->"
            );

            $exception = new Exception(
                "Generated XML is invalid http://{$this->serverName}/uploads/{$name}",
                E_PARSE
            );
            $exception->setWsdlErrors(libxml_get_errors());

            throw $exception;
        }
        libxml_use_internal_errors(false);
    }
}
