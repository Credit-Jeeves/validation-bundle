<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ExternalApiBundle\Services\MRI\MRIClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class ResManClientCase extends Base
{
    const PROPERTY_ID = '500';

    /**
     * @test
     */
    public function shouldReturnResidents()
    {
        $container = $this->getKernel()->getContainer();
        /** @var MRIClient $mriClient */
        $mriClient = $container->get('mri.client');
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\MRI\MRIClient',
            $mriClient
        );
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneBy(
            array(
                'name' => 'Rent Holding',
            )
        );

        $this->assertNotNull($holding);
        $mriSettings = $holding->getMriSettings();
        $this->assertNotNull($mriSettings);
        $mriClient->setSettings($mriSettings);
        $mriClient->setDebug(false);
        $mriResponse = $mriClient->getResidentTransactions(self::PROPERTY_ID);
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse', $mriResponse);
        $this->assertGreaterThan(15, $mriResponse->getValues());
        /** @var Value $value */
        $value = $mriResponse->getValues()[14];
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\Value', $value);
        $this->assertNotEmpty($value->getResidentId());
        $this->assertNotEmpty($value->getUnitId());
        $this->assertNotEmpty($value->getFirstName());
        $this->assertNotEmpty($value->getLastName());
        $this->assertNotEmpty($value->getLeaseBalance());
        $this->assertNotEmpty($value->getLeaseMonthlyRentAmount());
        $this->assertInstanceOf('\DateTime', $value->getLastUpdateDate());
        $this->assertInstanceOf('\DateTime', $value->getLeaseMoveOut());
        $this->assertInstanceOf('\DateTime', $value->getLeaseEnd());
        $this->assertInstanceOf('\DateTime', $value->getLeaseStart());

        file_put_contents('/var/www/Credit-Jeeves-SF2/mri_dump.txt', print_r($mriResponse, true));
    }
}
