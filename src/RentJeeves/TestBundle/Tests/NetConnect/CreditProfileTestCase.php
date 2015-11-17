<?php
namespace RentJeeves\TestBundle\Tests\NetConnect;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\ExperianBundle\NetConnect\Exception;
use CreditJeeves\TestBundle\Tests\NetConnect\CreditProfileTestCase as Base;
use RentJeeves\DataBundle\Entity\Tenant;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class CreditProfileTestCase extends Base
{
    /**
     * @var string
     */
    const APP = 'AppRj';

    protected function getTenant()
    {
        $tenant = new Tenant();
        $tenant->setEmail('tenant11@example.com');
        $tenant->setSsn('666042073');
        $tenant->setFirstName('Ton');
        $tenant->setLastName('Sharp');
        $address = new Address();
        $address->setIsDefault(true);
        $tenant->addAddress($address);

        return $tenant;
    }

    /**
     * @test
     */
    public function getResponseOnUserData()
    {
        $tenant = $this->getTenant();
        $this->assertStringEqualsFile(
            __DIR__ . '/../../Resources/NetConnect/CreditProfile/tenant11.arf',
            $this->getContainer()->get('experian.net_connect.credit_profile')->getResponseOnUserData($tenant)
        );
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function getResponseOnUserDataXmlException()
    {
        $tenant = $this->getTenant();
        $tenant->setEmail('tenant11111@example.com');
        $this->getContainer()->get('experian.net_connect.credit_profile')->getResponseOnUserData($tenant);
    }
}
