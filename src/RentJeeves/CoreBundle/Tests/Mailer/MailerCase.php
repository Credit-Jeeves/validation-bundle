<?php

namespace RentJeeves\CoreBundle\Tests\Mailer;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class MailerCase extends BaseTestCase
{
    /**
     * Should be like in \CreditJeeves\CoreBundle\Mailer\Mailer
     *
     * @var array
     */
    protected $defaultValuesForEmail = [
        'logoName' => 'logo_rj.png',
        'partnerName' => 'RentTrack',
        'partnerAddress' => '4601 Excelsior Blvd Ste 403A, St. Louis Park, MN 55416',
        'loginUrl' => 'my.renttrack.com',
        'isPoweredBy' => false
    ];

    /**
     * @test
     */
    public function shouldAddMandrillHeaderIfTemplateHasMandrillSlug()
    {
        $this->load(true);

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $template = $this->getEntityManager()->find('RjEmailBundle:EmailTemplate', 1);
        $template->getEnTranslation()->setBody('test');
        $template->getEnTranslation()->setMandrillSlug('testSlug');
        $this->getEntityManager()->flush($template);

        $this->getMailer()->sendBaseLetter('invite', [], 'test@mail.com', 'en');

        $this->assertCount(1, $plugin->getPreSendMessages());
        $message =  $plugin->getPreSendMessage(0);
        $header = $message->getHeaders();

        $this->assertTrue($header->has('X-MC-Track'));
        $this->assertEquals('opens, clicks_htmlonly', $header->get('X-MC-Track')->getFieldBody());
        $this->assertTrue($header->has('X-MC-GoogleAnalytics'));
        $this->assertEquals(
            'my.renttrack.com, www.renttrack.com, renttrack.com',
            $header->get('X-MC-GoogleAnalytics')->getFieldBody()
        );
        $this->assertTrue($header->has('X-MC-Tags'));
        $this->assertEquals('invite.html', $header->get('X-MC-Tags')->getFieldBody());
        $this->assertTrue($header->has('X-MC-Template'));
        $this->assertEquals('testSlug', $header->get('X-MC-Template')->getFieldBody());
        $this->assertTrue($header->has('X-MC-MergeVars'));
        $this->assertEquals(
            json_encode($this->defaultValuesForEmail, true),
            $header->get('X-MC-MergeVars')->getFieldBody()
        );

        $this->assertTrue($header->has('X-MC-URLStripQS'));
        $this->assertEquals('true', $header->get('X-MC-URLStripQS')->getFieldBody());
    }

    /**
     * @test
     */
    public function shouldSendOrderSendingNotification()
    {
        $this->load(true);

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $em = $this->getEntityManager();
        $order = $em->find('DataBundle:Order', 2);
        $this->getMailer()->sendOrderSendingNotification($order);

        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Your Rent Check is in the Mail!', $message->getSubject());
    }

    /**
     * @test
     */
    public function shouldSendOrderRefundingNotification()
    {
        $this->load(true);

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $em = $this->getEntityManager();
        $order = $em->find('DataBundle:Order', 2);
        $this->getMailer()->sendOrderRefundingNotification($order);

        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Your Rent Payment is being Refunded', $message->getSubject());
    }

    /**
     * @test
     */
    public function shouldSendOrderReissuedNotification()
    {
        $this->load(true);

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $em = $this->getEntityManager();
        $order = $em->find('DataBundle:Order', 2);
        $this->getMailer()->sendOrderReissuedNotification($order);

        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Your Rent Check has been Reissued!', $message->getSubject());
    }

    /**
     * @return \RentJeeves\CoreBundle\Mailer\Mailer
     */
    protected function getMailer()
    {
        return $this->getContainer()->get('project.mailer');
    }
}
