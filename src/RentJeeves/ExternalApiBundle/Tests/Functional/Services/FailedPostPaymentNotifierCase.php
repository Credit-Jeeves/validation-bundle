<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional\Services;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\ExternalApiBundle\Services\EmailNotifier\FailedPostPaymentNotifier;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class FailedPostPaymentNotifierCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldNotifyAdminLandlord()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(22);

        $this->assertNotEmpty($contract, 'Please check fixtures');

        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setStatus(OrderStatus::COMPLETE);
        $order->setPaymentType(OrderPaymentType::BANK);
        $order->setSum(600);
        $order->setPaymentProcessor(PaymentProcessor::ACI);
        $order->setDescriptor('Test Check');
        $order->setCreatedAt(new \DateTime());

        $operation = new Operation();
        $operation->setAmount(600);
        $operation->setType(OperationType::RENT);
        $operation->setOrder($order);
        $operation->setGroup(null);
        $operation->setContract($contract);
        $operation->setPaidFor(new \DateTime());

        $order->addOperation($operation);
        $this->getEntityManager()->persist($operation);
        $this->getEntityManager()->persist($order);

        $jobRelatedToOrder = new JobRelatedOrder();
        $jobRelatedToOrder->setOrder($order);
        $jobRelatedToOrder->setCreatedAt(new \DateTime());
        $job = new Job('external_api:payment:push');
        $job->addRelatedEntity($jobRelatedToOrder);
        $job->setState(Job::STATE_RUNNING);
        $job->setState(Job::STATE_FAILED);

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->persist($jobRelatedToOrder);

        $this->getEntityManager()->flush();
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        /** @var FailedPostPaymentNotifier $notifier */
        $notifier = $this->getContainer()->get('failed.post.payment.notifier');
        $notifier->notify($contract->getGroup()->getHolding());
        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Unable to Post Payment to Accounting System', $message->getSubject());
        $this->assertArrayHasKey(0, $message->getChildren(), 'Attachment should be');
        $this->assertArrayHasKey(1, $message->getChildren(), 'Body should be');
    }

    /**
     * @test
     */
    public function shouldNotifyNoneAdminLandlord()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(22);
        $holdingAdmins = $contract->getGroup()->getHolding()->getHoldingAdmin();
        $this->assertArrayHasKey(0, $holdingAdmins, 'Holding admins not return correct array');
        /** @var Landlord $landlordAdmin */
        $landlordAdmin = $holdingAdmins[0];
        $landlordAdmin->setIsHoldingAdmin(false);
        $this->assertNotEmpty($contract, 'Please check fixtures');

        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setStatus(OrderStatus::COMPLETE);
        $order->setPaymentType(OrderPaymentType::BANK);
        $order->setSum(600);
        $order->setPaymentProcessor(PaymentProcessor::ACI);
        $order->setDescriptor('Test Check');
        $order->setCreatedAt(new \DateTime());

        $operation = new Operation();
        $operation->setAmount(600);
        $operation->setType(OperationType::RENT);
        $operation->setOrder($order);
        $operation->setGroup(null);
        $operation->setContract($contract);
        $operation->setPaidFor(new \DateTime());

        $order->addOperation($operation);
        $this->getEntityManager()->persist($operation);
        $this->getEntityManager()->persist($order);

        $jobRelatedToOrder = new JobRelatedOrder();
        $jobRelatedToOrder->setOrder($order);
        $jobRelatedToOrder->setCreatedAt(new \DateTime());
        $job = new Job('external_api:payment:push');
        $job->addRelatedEntity($jobRelatedToOrder);
        $job->setState(Job::STATE_RUNNING);
        $job->setState(Job::STATE_FAILED);

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->persist($jobRelatedToOrder);

        $this->getEntityManager()->flush();
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        /** @var FailedPostPaymentNotifier $notifier */
        $notifier = $this->getContainer()->get('failed.post.payment.notifier');
        $notifier->notify($contract->getGroup()->getHolding());
        $this->assertCount(1, $plugin->getPreSendMessages(), '1 email should be sent');
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Unable to Post Payment to Accounting System', $message->getSubject());
        $this->assertArrayHasKey(0, $message->getChildren(), 'Attachment should be');
        $this->assertArrayHasKey(1, $message->getChildren(), 'Body should be');
    }
}
