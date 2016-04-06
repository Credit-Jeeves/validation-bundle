<?php

namespace RentJeeves\CoreBundle\Tests\Mailer;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PartnerUser;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\ExternalApiBundle\Model\EmailNotifier\FailedPostPaymentDetail;
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
        'isPoweredBy' => false,
        'replyToEmail' => 'help@renttrack.com'
    ];

    /**
     * @test
     */
    public function shouldUsePartnerNameForFrom()
    {
        $this->load(true);

        $plugin = $this->registerEmailListener();
        $plugin->clean();
        /** @var PartnerUser $partnerUser */
        $partnerUser = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:PartnerUser')
            ->findOneByEmail('anna_lee@example.com');

        $this->assertNotNull($partnerUser, 'Check fixtures, partner with email "anna_lee@example.com" should exist');
        // prepare fixtures
        $partnerUser->getPartner()->setPoweredBy(true);
        $this->getEntityManager()->flush($partnerUser);

        $this->getMailer()->sendRjCheckEmail($partnerUser);
        $this->assertCount(1, $plugin->getPreSendMessages(), 'Should be send just 1 message for checking email');
        $message =  $plugin->getPreSendMessage(0);

        $this->assertArrayHasKey('no-reply@renttrack.com', $message->getFrom(), 'Should have from key with email');

        $this->assertEquals(
            $partnerUser->getPartner()->getName(),
            $message->getFrom()['no-reply@renttrack.com'],
            sprintf('From Name on email should be like partner name "%s"', $partnerUser->getPartner()->getName())
        );
    }

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

        $this->getMailer()->sendBaseLetter('invite', [], $email = 'test@mail.com', 'en');

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

        $expectedParams = array_merge($this->defaultValuesForEmail, ['emailTo' => urlencode($email)]);
        $this->assertEquals(
            json_encode($expectedParams, true),
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

        $template = $this->getEntityManager()->getRepository('RjEmailBundle:EmailTemplate')
            ->findOneBy(['name' => 'rjOrderSending.html']);
        $subject = $template->translate('test')->getSubject();
        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals($subject, $message->getSubject());
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
     * @test
     */
    public function shouldSendEmailChurnRecaptureForOrder()
    {
        $this->load(true);

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $em = $this->getEntityManager();
        $order = $em->find('DataBundle:Order', 2);
        $this->getMailer()->sendChurnRecaptureForOrder($order);

        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Did you miss a rent payment?', $message->getSubject());
    }

    /**
     * @test
     */
    public function shouldSendScoreTrackError()
    {
        $this->load(true);

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $orders = $this->getEntityManager()->getRepository('DataBundle:Order')
            ->createQueryBuilder('o')
            ->select()
            ->leftJoin('o.operations', 'p')
            ->where('p.type = :report')
            ->setParameter('report', OperationType::REPORT)
            ->getQuery()
            ->execute();
        $this->assertNotEmpty($orders, 'Orders should exist in fixtures');
        /** @var Order $order */
        $order = reset($orders);
        $transaction = $this->getEntityManager()->getRepository('RjDataBundle:Transaction')->findOneBy(
            [
                'isSuccessful' => 0
            ]
        );
        $this->assertNotEmpty($transaction, 'Transaction should exist in fixtures');
        $transaction->setOrder($order);
        $transaction->setStatus(TransactionStatus::COMPLETE);
        $transaction->getTransactionId(12143);
        $transaction->setIsSuccessful(0);
        $transaction->setMessages('Test error message.');
        $order->setStatus(OrderStatus::ERROR);
        $order->addTransaction($transaction);
        $this->getEntityManager()->flush();

        $this->getMailer()->sendScoreTrackError($order);

        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('ScoreTrack Payment Error', $message->getSubject());
        $this->assertArrayHasKey(0, $message->getChildren(), 'Should have content');
        $this->assertContains('Test error message.', $message->getChildren()[0]->getBody());
    }

    /**
     * @test
     */
    public function sendReportReceipt()
    {
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $orders = $this->getEntityManager()->getRepository('DataBundle:Order')
            ->createQueryBuilder('o')
            ->select()
            ->leftJoin('o.operations', 'p')
            ->where('p.type = :report')
            ->setParameter('report', OperationType::REPORT)
            ->getQuery()
            ->execute();

        $this->assertNotEmpty($orders, 'Orders should exist in fixtures');
        $this->getMailer()->sendReportReceipt(reset($orders));

        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Receipt from Rent Track', $message->getSubject());
    }

    /**
     * @test
     */
    public function shouldSendPaymentFailureEmail()
    {
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $mailer = $this->getMailer();
        $landlord = new Landlord();
        $landlord->setCulture('test');
        $landlord->setEmail('test@email.com');
        $failureDetail = new FailedPostPaymentDetail();
        $failureDetail->setPaymentDate(new \DateTime());
        $failureDetail->setResidentId('123141');
        $failureDetail->setResidentName('Hello Hello');
        $failureDetail->setRentTrackBatchNumber('BatchNumber');

        $tempFilePath = tempnam(sys_get_temp_dir(), 'Temp file content');

        $mailer->sendPostPaymentError($landlord, [$failureDetail], $tempFilePath);

        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Unable to Post Payment to Accounting System', $message->getSubject());
        $this->assertArrayHasKey(0, $message->getChildren(), 'Attachment should be');
        $this->assertArrayHasKey(1, $message->getChildren(), 'Body should be');
    }


    /**
     * @return \RentJeeves\CoreBundle\Mailer\Mailer
     */
    protected function getMailer()
    {
        return $this->getContainer()->get('project.mailer');
    }
}
