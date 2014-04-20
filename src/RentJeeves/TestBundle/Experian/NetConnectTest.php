<?php
namespace RentJeeves\TestBundle\Experian;

use CreditJeeves\TestBundle\Experian\NetConnectTest as Base;
use JMS\DiExtraBundle\Annotation as DI;
use RuntimeException;

/**
 * DI\Service("experian.net_connect") It is deffined in services.yml
 */
class NetConnectTest extends Base
{
    protected function getResponse($aplicant)
    {
        switch ($aplicant->getEmail()) {
            case 'tenant11@example.com':
                return file_get_contents($this->getFixturesDir() . 'tenant11.xml');
        }
        throw new RuntimeException(sprintf('Please add fixture for user %s', $aplicant->getEmail()));
    }
}
