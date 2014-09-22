<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional;

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
        $receiptBatch = $this->getContainer()->get('yardi.push_batch_receipts');
        $date = new \DateTime();
        $date->modify('-359 days'); //DepositDate from rjCheckout_7_1_1
        $receiptBatch->run($date);
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
    }
}
