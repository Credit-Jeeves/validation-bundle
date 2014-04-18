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
    }

    /**
     * @DI\InjectParams({
     *     "config"     = @DI\Inject("experian.config")
     * })
     *
     * @param ExperianConfig $config
     */
    public function initConfigs($config)
    {
        parent::__construct();
    }

    /**
     * @param cjApplicant $applicant
     *
     * @return NetConnectResponse
     */
    public function getObjectOnUserData(Applicant $applicant, $xsdRequestPath = null)
    {
        $userData = $this->modelToData($applicant);
        $this->composeRequest($this->xml->userRequestXML($userData, $xsdRequestPath));
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

    public function execute()
    {
    }
}
