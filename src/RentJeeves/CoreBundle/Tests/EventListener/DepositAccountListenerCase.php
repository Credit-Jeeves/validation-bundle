<?php

namespace RentJeeves\CoreBundle\Tests\EventListener;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class DepositAccountListenerCase extends BaseTestCase
{
    /**
     * Deposit notify
     *
     * @test
     */
    public function sendEmail()
    {
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $depositAccount = $em->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            array('status' => DepositAccountStatus::DA_INIT)
        );
        $depositAccount->setMerchantName('test');
        // TODO this does not work, but should or not?
//        $em->persist($depositAccount);
//        $em->flush($depositAccount);
//        $this->assertCount(0, $plugin->getPreSendMessages());


        $depositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        $em->persist($depositAccount);
        $em->flush($depositAccount);

        $this->assertCount(1, $plugin->getPreSendMessages());

        $this->assertEquals(
            'Your RentTrack Merchant Account is Ready!',
            $plugin->getPreSendMessage(0)->getSubject()
        );
    }
}
