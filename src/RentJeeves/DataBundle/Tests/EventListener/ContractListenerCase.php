<?php

namespace RentJeeves\DataBundle\Tests\EventListener;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\TestBundle\BaseTestCase as Base;

class ContractListenerCase extends Base
{
    /**
     * @expectedException \Symfony\Component\Form\Exception\LogicException
     * @test
     */
    public function makeSureIntegratedFieldWillNotChangeFromTrueToFalse()
    {
        $this->load(true);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $groupSetting GroupSettings
         */
        $groupSetting = $em->getRepository('RjDataBundle:GroupSettings')->findOneBy(
            array(
                'isIntegrated' => true
            )
        );
        $this->assertNotNull($groupSetting);
        $groupSetting->setIsIntegrated(false);
        $em->persist($groupSetting);
        $em->flush();
    }

    /**
     * @test
     */
    public function monitoringContractAmount()
    {
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $payment Payment
         */
        $payment = $em->getRepository('RjDataBundle:Payment')->findOneBy(
            array(
                'status' => PaymentStatus::ACTIVE,
                'amount' => 1400
            )
        );
        $paymentId = $payment->getId();
        $contract = $payment->getContract();
        $contract->setRent($payment->getAmount() + 100);
        $em->persist($contract);
        $em->flush();

        $this->assertCount(1, $plugin->getPreSendMessages());

        $this->assertEquals(
            'Your Rent amount was adjusted on your contract',
            $plugin->getPreSendMessage(0)->getSubject()
        );

        static::$kernel = null;
        $payment = $this->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository('RjDataBundle:Payment')
            ->find($paymentId);
        $this->assertEquals(PaymentStatus::CLOSE, $payment->getStatus());
    }
}
