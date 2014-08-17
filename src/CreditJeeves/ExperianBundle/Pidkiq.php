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
 * DI\Service("experian.pidkiq") It is deffined in services.yml
 */
class Pidkiq extends \Pidkiq
{
    protected $isLogging = false;

    protected $logPath;

    protected $serializer = null;

    protected $lastResponse;

    /**
     * @see Experian::userToData
     * @var array
     */
    protected $userToData = array(
        'Name' => array(
            'Surname' => 'getLastName',
            'First' => 'getFirstName',
            'Middle' => 'getMiddleInitial',
            'Gen' => '',
        ),
        'SSN' => 'getSsn',
        'CurrentAddress' => array(
            'Street' => 'getStreetAddress1',
            'City' => 'getCity',
            'State' => 'getState',
            'Zip' => 'getZip',
        ),
        'PreviousAddress' => '',
        'Phone' => array(
            'Number' => 'getPhone',
            'Type' => '',
        ),
        'Employment' => '',
        'Age' => '',
        'DOB' => 'getDBO', // date_of_birth
        'YOB' => '',
        'MothersMaidenName' => '',
        'EmailAddress' => '', // email
    );

    public function __construct()
    {
    }

    /**
     * DI\InjectParams({ It is deffined in services.yml
     *     "config"     = DI\Inject("experian.config"),
     *     "isLogging"  = DI\Inject("%experian.logging%"),
     *     "logPath"    = DI\Inject("%kernel.logs_dir%"),
     *
     * })
     *
     * @param ExperianConfig $config
     * @param bool $isLogging
     * @param string $logPath
     */
    public function initConfigs($config, $isLogging, $logPath)
    {
        $this->isLogging = $isLogging;
        $this->logPath = $logPath;

        parent::__construct();
    }

    public function execute()
    {
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
