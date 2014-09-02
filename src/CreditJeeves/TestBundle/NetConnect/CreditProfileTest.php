<?php
namespace CreditJeeves\TestBundle\NetConnect;

use CreditJeeves\ExperianBundle\NetConnect\CreditProfile as Base;
use JMS\DiExtraBundle\Annotation as DI;
use RuntimeException;

/**
 * DI\Service("experian.net_connect.credit_profile") It is deffined in services.yml
 */
class CreditProfileTest extends Base
{
    protected $dataDir;

    /**
     * DI\InjectParams({ It is deffined in services.yml
     *     "dataDir" = DI\Inject("%data.dir%"),
     * })
     *
     * @param string $dataDir
     */
    public function setDataDirPath($dataDir)
    {
        $this->dataDir = $dataDir;
    }

    protected function getFixturesDir()
    {
        return $this->dataDir . '/experian/netConnect/';
    }

    protected function getResponse($aplicant)
    {
        switch ($aplicant->getEmail()) {
            case 'emilio@example.com':
                return file_get_contents($this->getFixturesDir() . 'emilio.xml');
            case 'marion@example.com':
                return file_get_contents($this->getFixturesDir() . 'marion.xml');
            case 'alex@example.com':
                return file_get_contents($this->getFixturesDir() . 'alex.xml');
            case 'mamazza@example.com':
                return file_get_contents($this->getFixturesDir() . 'mamazza.xml');
            case 'john@example.com':
                return file_get_contents($this->getFixturesDir() . 'john.xml');
            case 'app14@example.com':
                return file_get_contents($this->getFixturesDir() . 'app14.xml');
            case 'robert@example.com':
                return file_get_contents($this->getFixturesDir() . 'robert.xml');
            case 'alexey.karpik+app1334753295955955@gmail.com':
                return file_get_contents($this->getFixturesDir() . 'alexey.karpik.xml');
        }
        throw new RuntimeException(sprintf('Please add fixture for user %s', $aplicant->getEmail()));
    }

    public function getResponseOnUserData($aplicant)
    {
        $responce = $this->getResponse($aplicant);
        $this->xml->__construct();
        $this->xml->userRequestXML($this->addUserToRequest($aplicant)); // It need to pass XML validation

        return $this->retriveUserDataFromXML($responce);
    }
}
