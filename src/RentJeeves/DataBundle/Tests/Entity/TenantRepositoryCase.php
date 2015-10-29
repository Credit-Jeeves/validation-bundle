<?php

namespace RentJeeves\DataBundle\Tests\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\BaseTestCase;

class TenantRepositoryCase extends BaseTestCase
{
    /**
     * @return array
     */
    public function dataForCheckLockPaymentProcessor()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider dataForCheckLockPaymentProcessor
     * @test
     *
     * @var boolean $isPaymentProcessorLocked
     */
    public function checkLockPaymentProcessor($isPaymentProcessorLocked)
    {
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding);
        $holding->setIsPaymentProcessorLocked($isPaymentProcessorLocked);
        $em->flush();
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        $this->assertNotEmpty($tenant);
        $this->assertEquals(
            $em->getRepository('RjDataBundle:Tenant')->isPaymentProcessorLocked($tenant),
            $isPaymentProcessorLocked
        );
    }
}
