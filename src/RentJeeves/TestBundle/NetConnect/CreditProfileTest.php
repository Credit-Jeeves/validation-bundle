<?php
namespace RentJeeves\TestBundle\NetConnect;

use CreditJeeves\TestBundle\NetConnect\CreditProfileTest as Base;
use JMS\DiExtraBundle\Annotation as DI;
use RuntimeException;

/**
 * DI\Service("experian.net_connect.credit_profile") It is defined in services.yml
 */
class CreditProfileTest extends Base
{
    protected function getFixturesDir()
    {
        return __DIR__ . '/../Resources/experian/netConnect/';
    }

    protected function getResponse($aplicant)
    {
        switch ($aplicant->getEmail()) {
            case 'tenant11@example.com':
                return file_get_contents($this->getFixturesDir() . 'tenant11.xml');
            case 'mamazza@rentrack.com':
                return file_get_contents($this->getFixturesDir() . 'mamazza.xml');
            case 'marion@rentrack.com':
                return file_get_contents($this->getFixturesDir() . 'marion.xml');
        }
        throw new RuntimeException(sprintf('Please add fixture for user %s', $aplicant->getEmail()));
    }
}
