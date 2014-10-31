<?php
namespace CreditJeeves\DataBundle\Tests\Entity;

use RentJeeves\TestBundle\BaseTestCase;

class PaymentResourceCase extends BaseTestCase
{

    /**
     * @test
     */
    public function getPaymentByUserId()
    {
        $this->load(true);
        $container = $this->getContainer();
        $em = $container->get('doctrine');

        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );

        $repo = $em->getRepository('RjDataBundle:Payment');
        $payment = $repo->findOneByIdForUser(1, $tenant);

        $this->assertEquals('recurring', $payment->getType());
    }
}
