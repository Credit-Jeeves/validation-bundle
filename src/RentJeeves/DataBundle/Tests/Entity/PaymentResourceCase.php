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

        $repo = $em->getRepository('RjDataBundle:Payment');
        $payment = $repo->findOneByIdForUser(1, 'tenant11@example.com');

        $this->assertEquals('recurring', $payment->getType());
    }

}