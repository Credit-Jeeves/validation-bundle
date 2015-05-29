<?php
namespace RentJeeves\TestBundle\Tests;

use Monolog\Logger;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Ton\EmailBundle\Message;

class MonologSwiftCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSendEmailAfterCallingLogger()
    {
        $this->clearEmail();
        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');
        $logger->alert('Alert Message');
        $logger->emergency('Emergency Message');

        $this->assertCount(2, $emails = $this->getEmails());
        /** @var Message $alertMessage */
        $alertMessage = $this->getEmailReader()->getEmail($emails[0]);

        $emailFrom = $this->getContainer()->getParameter('email_from');

        $alertEmailTo = $this->getContainer()->getParameter('monolog.alert.email_to');
        $this->assertEquals('Alert log!', $alertMessage->getHeaders()->get('Subject')->getFieldValue());
        $this->assertEquals($emailFrom, $alertMessage->getHeaders()->get('From')->getFieldValue());
        $this->assertEquals($alertEmailTo, $alertMessage->getHeaders()->get('X-Swift-To')->getFieldValue());
        $this->assertContains('Alert Message', $alertMessage->getContent());
        /** @var Message $emergencyMessage */
        $emergencyMessage = $this->getEmailReader()->getEmail($emails[1]);

        $emergencyEmailTo = $this->getContainer()->getParameter('monolog.emergency.email_to');
        $this->assertEquals('Emergency log!', $emergencyMessage->getHeaders()->get('Subject')->getFieldValue());
        $this->assertEquals($emailFrom, $emergencyMessage->getHeaders()->get('From')->getFieldValue());
        $this->assertEquals(
            $emergencyEmailTo,
            $emergencyMessage->getHeaders()->get('X-Swift-To')->getFieldValue()
        );
        $this->assertContains('Emergency Message', $emergencyMessage->getContent());
    }
}
