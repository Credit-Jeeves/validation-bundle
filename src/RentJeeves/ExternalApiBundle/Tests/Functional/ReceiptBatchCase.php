<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\ExternalApiBundle\Services\Yardi\ReceiptBatchSender;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ReceiptBatchCase extends BaseTestCase
{
    /**
     * In this case we use fixures rjCheckout_7_1_1
     * and must send email to Landlord_1
     *
     * @test
     */
    public function shouldReceiptBatch()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant11 Tenant
         */
        $tenant11 = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );
        /**
         * @var $residentMapping ResidentMapping
         */
        $residentMapping = $tenant11->getResidentsMapping()->first();
        $residentMapping->setResidentId('t0012027');
        $em->flush($residentMapping);
        static::$kernel = null;

        /** @var $receiptBatch ReceiptBatchSender */
        $receiptBatch = $this->getContainer()->get('yardi.push_batch_receipts');
        $date = new \DateTime();
        $date->modify('-359 days'); //DepositDate from rjCheckout_7_1_1
        $receiptBatch->run($date);
        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
    }
}
