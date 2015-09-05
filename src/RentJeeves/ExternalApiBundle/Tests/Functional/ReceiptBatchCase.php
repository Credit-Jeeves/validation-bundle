<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\SynchronizationStrategy;
use RentJeeves\DataBundle\Enum\YardiPostMonthOption;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;
use RentJeeves\ExternalApiBundle\Command\YardiBatchReceiptCommand;
use RentJeeves\ExternalApiBundle\Services\Yardi\ReceiptBatchSender;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\PaymentClientCase;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ReceiptBatchCase extends BaseTestCase
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    /**
     * In this case we use fixtures rjCheckout_7_1_1
     * and must send email to Landlord_1
     *
     * @test
     */
    public function shouldReceiptBatch()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var Tenant $tenant11 */
        $tenant11 = $em->getRepository('RjDataBundle:Tenant')->findOneBy(['email' => 'tenant11@example.com']);
        /** @var ResidentMapping $residentMapping */
        $residentMapping = $tenant11->getResidentsMapping()->first();
        $residentMapping->setResidentId(PaymentClientCase::RESIDENT_ID);
        $em->flush($residentMapping);

        static::$kernel = null;

        /** @var ReceiptBatchSender $receiptBatch  */
        $receiptBatch = $this->getContainer()->get('yardi.push_batch_receipts');
        $date = new \DateTime();
        $date->modify('-359 days'); //DepositDate from rjCheckout_7_1_1
        $receiptBatch->run($date);
        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
    }

    /**
     * @return array
     */
    public function provideYardiPostMonthNodeOption()
    {
        return [
            [YardiPostMonthOption::TRANSACTION_DATE],
            [YardiPostMonthOption::DEPOSIT_DATE],
        ];
    }

    /**
     * @test
     * @dataProvider provideYardiPostMonthNodeOption
     */
    public function shouldSendPaymentToYardiApiWithPostMonthNode($postMonthOption)
    {
        $this->load(true);
        $em = $this->getEntityManager();

        /** @var Holding $holding */
        $holding = $em->find('DataBundle:Holding', 5);
        $this->assertNotNull($holding, 'Holding not found');

        $yardiSettings = $holding->getYardiSettings();
        $this->assertNotNull($yardiSettings, 'Holding \'Rent Holding\' doesn\'t have Yardi Settings');
        $yardiSettings->setPostMonthNode($postMonthOption);
        $em->flush($holding->getYardiSettings());

        $transaction = $this->createTransaction(
            ApiIntegrationType::YARDI_VOYAGER,
            PaymentClientCase::RESIDENT_ID,
            PaymentClientCase::PROPERTY_ID,
            PaymentClientCase::RESIDENT_ID,
            null
        );
        $transaction->setDepositDate(new \DateTime());
        $em->flush($transaction);

        $application = new Application($this->getKernel());
        $application->add(new YardiBatchReceiptCommand());

        $command = $application->find('api:yardi:push-batch-receipts');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $order = $em->getRepository('RjDataBundle:OrderExternalApi')->findOneBy(['order' => $transaction->getOrder()]);
        $this->assertNotNull($order, 'Order should be saved to OrderExternalApi if it was sent successfully');
    }
}
