<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class PaymentCase extends BaseTestCase
{
    use AdminFormUniqueIdGetter;

    /**
     * @test
     */
    public function shouldSetCloseReasonWhenAdminClosesPayment()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->loginByAccessToken('admin@creditjeeves.com', $this->getUrl() . 'admin/rentjeeves/data/payment/list');
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        $this->assertCount(6, $editLinks = $table->findAll('css', '.edit_link'));
        $editLinks[0]->click();

        $uniqueId = $this->getUniqueId();

        $paymentStatus = $this->getDomElement('#' . $uniqueId . '_status');
        $paymentStatus->setValue(PaymentStatus::CLOSE);
        $startDate = new \DateTime('next month');
        $paymentStartYear = $this->getDomElement('#' . $uniqueId . '_startYear');
        $paymentStartYear->setValue($startDate->format('Y'));
        $paymentStartMonth = $this->getDomElement('#' . $uniqueId . '_startMonth');
        $paymentStartMonth->setValue($startDate->format('n'));

        $this->page->pressButton('btn_update_and_edit');

        $em = $this->getEntityManager();
        /** @var Payment $payment */
        $payment = $em->find('RjDataBundle:Payment', 1);
        $this->assertNotNull($payment, 'Not found payment #1');
        $this->assertEquals(PaymentStatus::CLOSE, $payment->getStatus(), 'Payment status should be updated to CLOSE');
        $this->assertNotEmpty($closeDetails = $payment->getCloseDetails(), 'Close details should be filled in!');
        $this->assertCount(2, $closeDetails, 'Close details should have 2 items');
        $this->assertEquals('Reason: closed_by_admin', $closeDetails[1], 'Close details should have a reason');
    }

    /**
     * @test
     */
    public function butchRun()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->loginByAccessToken('admin@creditjeeves.com', $this->getUrl() . 'admin/rentjeeves/data/payment/list');

        $this->assertNotNull($table = $this->page->find('css', 'table'));
        $this->assertNotNull($checkBoxes = $table->findAll('css', '.sonata-ba-list-field input'));
        $this->assertCount(6, $checkBoxes);

        foreach ($checkBoxes as $checkBox) {
            $checkBox->check();
        }
        $this->page->pressButton('btn_batch');
        $this->page->pressButton('btn_execute_batch_action');

        $this->assertNotNull($alert = $this->page->find('css', '.alert-success'), 'Alert not found');
        $this->assertEquals('× admin.butch.run.success-1', $alert->getText());

        foreach ($checkBoxes as $checkBox) {
            $checkBox->check();
        }
        $this->page->pressButton('btn_batch');
        $this->page->pressButton('btn_execute_batch_action');

        $this->assertNotNull($alert = $this->page->find('css', '.alert-warning'));
        $this->assertEquals('admin.butch.run.warning', $alert->getText());
    }
}
