<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\ExternalApiBundle\Services\Yardi\ReceiptBatchSender;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\PaymentClientCase;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ReceiptBatchCase extends BaseTestCase
{
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
}
