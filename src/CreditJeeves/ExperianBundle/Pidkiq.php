<?php
namespace CreditJeeves\ExperianBundle;

use CreditJeeves\ApiBundle\Util\ExceptionWrapper;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use \ExperianException;
use \Xml;

require_once __DIR__.'/../CoreBundle/sfConfig.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/curl/Curl.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/curl/CurlException.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/xml/Xml.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/ExperianException.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/ExperianCurl.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/Experian.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/ExperianXmlException.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/ExperianXml.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/pidkiq/Pidkiq.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/pidkiq/PidkiqCurl.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/pidkiq/PidkiqXml.class.php';

/**
 * Precise ID.
 * Pidkiq is used for verifying user's identity.
 *
 * @DI\Service("experian.pidkiq")
 */
class Pidkiq extends \Pidkiq
{
    protected $isLogging = false;

    protected $logPath;

    protected $serializer = null;

    protected $lastResponse;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @DI\InjectParams({
     *     "serverName"     = @DI\Inject("%server_name%"),
     *     "em"             = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "isLogging"      = @DI\Inject("%experian.logging%"),
     *     "logPath"        = @DI\Inject("%kernel.logs_dir%"),
     *
     * })
     *
     * @param string $serverName
     * @param EntityManager $em
     */
    public function initConfigs($serverName, EntityManager $em, $isLogging, $logPath)
    {
        $this->isLogging = $isLogging;
        $this->logPath = $logPath;
        \sfConfig::set('global_host', $serverName);
        /** @var \CreditJeeves\DataBundle\Entity\Settings $settings */
        $settings = $em->getRepository('DataBundle:Settings')->find(1);
        if (empty($settings)) {
            return;
        }
        \sfConfig::set('experian_pidkiq_userpwd', $settings->getPidkiqPassword());
        $xmlRoot = \sfConfig::get('experian_pidkiq_XML_root');
        $xmlRoot['EAI'] = $settings->getPidkiqEai();
        \sfConfig::set('experian_pidkiq_XML_root', $xmlRoot);
    }

    public function execute()
    {
        parent::__construct();
    }

    protected function getSerializer()
    {
        if (!is_null($this->serializer)) {
            return $this->serializer;
        }
        $this->serializer = SerializerBuilder::create()
            ->setPropertyNamingStrategy(
                new SerializedNameAnnotationStrategy(
                    new CamelCaseNamingStrategy('', false)
                )
            )
            ->build();

        return $this->serializer;
    }

    /**
     * @param User $user
     * @param string $xsdRequestPath
     *
     * @return NetConnectResponse
     */
    public function getObjectOnUserData($user, $xsdRequestPath = null)
    {
        $userData = $this->modelToData($user);
        $xml = $this->xml->userRequestXML($userData, $xsdRequestPath);
        if ($this->isLogging) {
            file_put_contents(
                $this->logPath . '/experian/' . str_replace('\\', '-', get_called_class()) . '.xml',
                $xml
            );
        }

        $responce = $this->curl->sendPostRequest($this->composeRequest($xml));
        if ($this->isLogging) {
            file_put_contents(
                $this->logPath . '/experian/' . str_replace('\\', '-', get_called_class()) . '-Response.xml',
                $responce
            );
        }

        $this->lastResponse = $responce;
        /**
         * @var NetConnectResponse $netConnectResponse
         */
        $netConnectResponse = $this->getSerializer()->deserialize(
            $responce,
            'CreditJeeves\ExperianBundle\Model\NetConnectResponse',
            'xml'
        );

        $products = $netConnectResponse->getProducts();
        if (!$products) {
            throw new ExperianException("Don't have 'Products' in responce");
        }
        $preciseIDServer = $products->getPreciseIDServer();
        if (!$preciseIDServer) {
            throw new ExperianException("Don't have 'PreciseIDServer' in responce");
        }
        $error = $preciseIDServer->getError();
        if ($error && !$preciseIDServer->getSummary()) {
            throw new ExperianException($error->getErrorDescription(), $error->getErrorCode());
        }

        $sharedApplication = $preciseIDServer->getGLBDetail()->getSharedApplication();
        $errors = $sharedApplication->getArrayOfErrors();
        if (!empty($errors) && isset($errors['3001'])) {
            throw new ExperianException(implode(ExceptionWrapper::SEPARATOR, $errors), 400);
        }

        return $netConnectResponse;
    }

    public function getLastResponce()
    {
        return $this->lastResponse;
    }

    /**
     * Check XML on errors
     *
     * @param Xml $doc
     *
     * @throws ExperianException
     */
    protected function checkErrors(Xml $doc)
    {
        $dom = $doc->getDom();
        for ($i = 1; $i <= 20; $i++) {
            $GLBRule = $dom->getElementsByTagName('GLBRule' . $i);
            if (!$GLBRule && !is_object($GLBRule->item(0))) {
                continue;
            }
            if ($GLBRule = $GLBRule->item(0)) {
                $code    = $GLBRule->getAttribute('code');
                $value   = $GLBRule->nodeValue;
                if ($code === '3001') {
                    throw new ExperianException($value, E_ALL);
                }
            }
        }

        return parent::checkErrors($doc);
    }
}
