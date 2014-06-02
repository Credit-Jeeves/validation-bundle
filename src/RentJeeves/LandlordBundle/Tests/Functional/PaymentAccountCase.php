<?php

namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class PaymentAccountCase extends BaseTestCase
{
    /**
     * @test
     */
    public function createPaymentAccount()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('common.account');
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
        $this->page->clickLink('settings.deposit');
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
        $this->session->wait($this->timeout, "$('.add-accoun').is(':visible')");
        $this->page->clickLink('add.account');
        $this->assertNotNull($form = $this->page->find('css', '#billingAccountType'));
        $this->fillForm(
            $form,
            array(
                'billingAccountType_nickname'         => "mary",
                'billingAccountType_PayorName'        => "mary stone",
                'billingAccountType_AccountNumber'    => "123245678",
                'billingAccountType_RoutingNumber'    => "062202574",
                'billingAccountType_ACHDepositType_0' => true,
                'billingAccountType_isActive'         => true,
            )
        );
        $this->assertNotNull($save = $this->page->find('css', '#save_payment'));
        $save->click();
        $this->session->wait(
            $this->timeout + 30000,
            "!$('#billingAccountType').is(':visible')"
        );
        $this->assertNotNull($account = $this->page->findAll('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('mary (settings.payment_account.active)', $account[0]->getText());

        $this->page->clickLink('add.account');
        $this->fillForm(
            $form,
            array(
                'billingAccountType_nickname'         => "gary",
                'billingAccountType_PayorName'        => "mary stone",
                'billingAccountType_AccountNumber'    => "123245678",
                'billingAccountType_RoutingNumber'    => "062202574",
                'billingAccountType_ACHDepositType_0' => true,
                'billingAccountType_isActive'         => true,
            )
        );
        $save->click();
        $this->session->wait($this->timeout, "$('.processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('.processPayment').is(':visible')");
        $this->assertNotNull($accounts = $this->page->findAll('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals(4, count($accounts));

        $this->assertEquals('mary', $accounts[0]->getText());
        $this->assertEquals('gary (settings.payment_account.active)', $accounts[2]->getText());

        //createPaymentAccount
        $this->assertNotNull($edit = $this->page->findAll('css', '.properties-table>tbody>tr>td>a'));
        $this->assertEquals(4, count($edit));
        $edit[0]->click();
        $this->assertNotNull($form = $this->page->find('css', '#billingAccountType'));
        $this->fillForm(
            $form,
            array(
                'billingAccountType_nickname'         => "mary less",
                'billingAccountType_isActive'         => true,
            )
        );
        $this->assertNotNull($save = $this->page->find('css', '#save_payment'));
        $save->click();
        $this->session->wait($this->timeout, "$('.processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('.processPayment').is(':visible')");
        $this->assertNotNull($accounts = $this->page->findAll('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals(4, count($accounts));

        $this->assertEquals('mary less (settings.payment_account.active)', $accounts[0]->getText());
        $this->assertEquals('gary', $accounts[2]->getText());
    }

    /**
     * @test
     * @depends createPaymentAccount
     */
    public function deletePaymentAccount()
    {
        $this->assertNotNull($delete = $this->page->findAll('css', '.properties-table>tbody>tr>td>a.delete'));
        $this->assertEquals(2, count($delete));
        $delete[1]->click();
        $this->assertNotNull($del = $this->page->find('css', '#billing-account-delete-form>a'));
        $del->click();
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertNotNull($accounts = $this->page->findAll('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals(2, count($accounts));

        $this->logout();
    }
}
