<?php

namespace CreditJeeves\CoreBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class MailerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldUseReplyToEmailIfNoReplyFalse()
    {
        $emailPlugin = $this->registerEmailListener();
        $emailPlugin->clean();
        $this
            ->getContainer()
            ->get('project.mailer')
            ->sendBaseLetter('invite', [] ,'tenant11@example.com', 'en', null, false);

        $this->assertCount(1, $emailPlugin->getPreSendMessages(), 'Should be send 1 message');

        $this->assertEquals(
            ['help@renttrack.com' => 'RentTrack'],
            $emailPlugin->getPreSendMessage(0)->getReplyTo(),
            'Reply to email should be help@renttrack.com'
        );
    }

    /**
     * @test
     */
    public function shouldNotUseReplyToEmailIfNoReplyTrue()
    {
        $emailPlugin = $this->registerEmailListener();
        $emailPlugin->clean();
        $this
            ->getContainer()
            ->get('project.mailer')
            ->sendBaseLetter('invite', [] ,'tenant11@example.com', 'en');

        $this->assertCount(1, $emailPlugin->getPreSendMessages(), 'Should be send 1 message');

        $this->assertNull(
            $emailPlugin->getPreSendMessage(0)->getReplyTo(),
            'Reply to email should be empty'
        );
    }
}
