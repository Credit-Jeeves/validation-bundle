<?php
namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class SourcesCase extends BaseTestCase
{
    /**
     * @test
     */
    public function edit()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-row-1').length");
        $this->assertNotNull($row = $this->page->find('css', '#payment-account-row-1'));
        $row->clickLink('edit');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymentaccounttype_name:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true
            )
        );

        $this->page->pressButton('payment_account.edit.save');

        $this->session->wait($this->timeout, "jQuery('#payment-account-edit .attention-box li').length");
        $this->assertNotNull($errors = $this->page->findAll('css', '#payment-account-edit .attention-box li'));
        $this->assertCount(5, $errors);

        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'New Card',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '123',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('j'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 1,
//                'rentjeeves_checkoutbundle_paymentaccounttype_address_choice_24' => true,
            )
        );
        $this->assertNotNull(
            $choices = $this->page->findAll(
                'css',
                '#rentjeeves_checkoutbundle_paymentaccounttype_address_choice_box i'
            )
        );
        $this->assertCount(2, $choices);
        $choices[1]->click();



        $this->page->pressButton('payment_account.edit.save');

        $this->session->wait($this->timeout + 15000, "jQuery('#payment-account-row-1 td:first').text() == 'New Card'");

        $this->assertNotNull($cols = $this->page->findAll('css', '#payment-account-row-1 td'));
        $this->assertEquals('New Card', $cols[0]->getText());

        $this->logout();
    }

    /**
     * @test
     */
    public function editTheSame()
    {
        $this->setDefaultSession('selenium2');
        $this->load(false);
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-row-2').length");
        $this->assertNotNull($row = $this->page->find('css', '#payment-account-row-2'));
        $row->clickLink('edit');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymentaccounttype_name:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Edited',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '123',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('j'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 1,
//                'rentjeeves_checkoutbundle_paymentaccounttype_address_choice_24' => true,
            )
        );
        $this->assertNotNull(
            $choices = $this->page->findAll(
                'css',
                '#rentjeeves_checkoutbundle_paymentaccounttype_address_choice_box i'
            )
        );
        $this->assertCount(2, $choices);
        $choices[1]->click();

        $this->page->pressButton('payment_account.edit.save');

        $this->session->wait($this->timeout, "jQuery('#payment-account-row-2 td:first').text() == 'Edited'");

        $this->logout(); // FIXME remove
        $this->markTestIncomplete('Functional have strange bug');//FIXME fix bug and run test

        $this->assertNotNull($cols = $this->page->findAll('css', '#payment-account-row-2 td'));
        $this->assertEquals('Edited', $cols[0]->getText());
    }

    /**
     * @test
     */
    public function del()
    {
        $this->setDefaultSession('selenium2');
        $this->load(false);
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('rent.sources');


        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");
        $this->assertNotNull($rows = $this->page->findAll('css', '#payment-account-table tbody tr'));
        $this->assertCount(2, $rows);

        $rows[1]->clickLink('delete');
        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');

        $this->session->wait($this->timeout, "1 == jQuery('#payment-account-table tbody tr').length");
        $this->assertNotNull($rows = $this->page->findAll('css', '#payment-account-table tbody tr'));
        $this->assertCount(1, $rows);
        $this->logout();
    }
}
