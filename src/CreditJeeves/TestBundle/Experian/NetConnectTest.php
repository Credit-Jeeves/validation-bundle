<?php
namespace CreditJeeves\TestBundle\Experian;

use CreditJeeves\ExperianBundle\NetConnect as Base;
use JMS\DiExtraBundle\Annotation as DI;
use RuntimeException;

/**
 * @DI\Service("experian.net_connect")
 */
class NetConnectTest extends Base
{
    protected $dataDir;

    /**
     * @DI\InjectParams({
     *     "config" = @DI\Inject("%data.dir%"),
     * })
     *
     * @param string $dataDir
     */
    public function setDataDirPath($dataDir)
    {
        $this->dataDir = $dataDir;
    }

    public function getResponseOnUserData($aplicant)
    {
        $this->xml->__construct();
        $this->xml->userRequestXML($this->modelToData($aplicant)); // It need to pass XML validation

        $fixturesDir = $this->dataDir . '/experian/netConnect/';

        switch ($aplicant->getEmail()) {
            case 'emilio@example.com':
                $responce = file_get_contents($fixturesDir . 'emilio.arf');
                break;
            case 'marion@example.com':
                $responce = file_get_contents($fixturesDir . 'marion.arf');
                break;
            default:
                throw new RuntimeException();
        }

        return $this->retriveUserDataFromXML($responce);
    }
}
