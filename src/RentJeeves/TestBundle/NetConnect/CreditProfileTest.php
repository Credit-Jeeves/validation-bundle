<?php
namespace RentJeeves\TestBundle\NetConnect;

use CreditJeeves\ExperianBundle\NetConnect\CreditProfile as Base;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\TestBundle\NetConnect\Traits\CreditProfileTest as CreditProfileTestTrait;
use RentJeeves\DataBundle\Entity\Tenant;
use RuntimeException;

/**
 * DI\Service("experian.net_connect.credit_profile") It is deffined in services.yml
 */
class CreditProfileTest extends Base
{
    use CreditProfileTestTrait;

    protected function getFixturesDir()
    {
        return __DIR__ . '/../Resources/NetConnect/CreditProfile/';
    }

    /**
     * @param Tenant $tenant
     */
    protected function getResponse($tenant)
    {
        switch ($tenant->getEmail()) {
            case 'tenant11@example.com':
                return file_get_contents($this->getFixturesDir() . 'tenant11.xml');
            case 'mamazza@rentrack.com':
                return file_get_contents($this->getFixturesDir() . 'mamazza.xml');
            case 'transU@example.com':
                return file_get_contents($this->getFixturesDir() . 'marion.xml');
            case 'marion@rentrack.com':
                return file_get_contents($this->getFixturesDir() . 'marion.xml');
        }
        throw new RuntimeException(sprintf('Please add fixture for user %s', $tenant->getEmail()));
    }
}
