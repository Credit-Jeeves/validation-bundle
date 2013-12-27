<?php
namespace CreditJeeves\TestBundle\Experian;

use CreditJeeves\DataBundle\Entity\Applicant;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;

require_once __DIR__ . '/../../ExperianBundle/Pidkiq.php';
require_once __DIR__ . '/../../../../vendor/credit-jeeves/credit-jeeves/lib/experian/pidkiq/PidkiqTest.class.php';

/**
 * @DI\Service("experian.pidkiq")
 */
class PidkiqTest extends \PidkiqTest
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @DI\InjectParams({
     *     "serverName" = @DI\Inject("%server_name%"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     * })
     *
     * @param string $serverName
     * @param EntityManager $em
     */
    public function initConfigs($serverName, EntityManager $em)
    {
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

    /**
     * @param cjApplicant $applicant
     *
     * @return NetConnectResponse
     */
    public function getObjectOnUserData(Applicant $applicant)
    {
        $userData = $this->modelToData($applicant);
        $this->composeRequest($this->xml->userRequestXML($userData));
        $responce = file_get_contents($this->fixturesDir . 'QuestionsResponse.xml');
        $serializer = SerializerBuilder::create()
            ->setPropertyNamingStrategy(
                new SerializedNameAnnotationStrategy(
                    new CamelCaseNamingStrategy('', false)
                )
            )
            ->build();

        /**
         * @var NetConnectResponse $netConnectResponse
         */
        $netConnectResponse = $serializer->deserialize(
            $responce,
            'CreditJeeves\ExperianBundle\Model\NetConnectResponse',
            'xml'
        );
        return $netConnectResponse;
    }
}
