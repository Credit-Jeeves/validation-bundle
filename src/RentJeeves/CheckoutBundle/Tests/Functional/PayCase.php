<?php
namespace RentJeeves\CheckoutBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class PayCase extends BaseTestCase
{
    /**
     * @test
     */
    public function recurring()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->page->pressButton('contract-pay-1');
        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());

        $this->assertNotNull(
            $propertyAddress = $this->page->find(
                'css',
                '#rentjeeves_checkoutbundle_paymenttype_property_address'
            )
        );
        $this->assertEquals('770 Broadway, Manhattan, #2-a New York, NY 10003 *required', $propertyAddress->getText());
        $this->assertNotNull($closeButton = $payPopup->find('css', '.ui-dialog-titlebar-close'));
        $closeButton->click();

        $this->page->pressButton('contract-pay-2');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait($this->timeout, "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length");
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_amount' => '0',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
            )
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('checkout.error.amount.min', $errors[0]->getText());

        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1500'
            )
        );


        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout,
            "jQuery('#id-source-step:visible').length"
        );

        $this->page->clickLink('common.add_new');



        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");
        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(5, $errors);

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test payment',
                'rentjeeves_checkoutbundle_paymentaccounttype_PayorName' => '????????',
                'rentjeeves_checkoutbundle_paymentaccounttype_RoutingNumber' => '062202574',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_ACHDepositType_0' => true,
            )
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 15000,
            "jQuery('#rentjeeves_checkoutbundle_userdetailstype_date_of_birth_month:visible').length"
        );

        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");
        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('checkout.error.address_choice.empty', $errors[0]->getText());

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_userdetailstype');
        $form->clickLink('common.add_new');

        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_userdetailstype_new_address_street' => '',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_city' => '',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_zip' => '',
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length == 3");
        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(3, $errors);


        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_userdetailstype_new_address_street' => 'New street',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_city' => 'New city',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_area' => 'NY',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_zip' => '99999',
            )
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout,
            "jQuery(':visible > #questions_OutWalletAnswer1_0').length"
        );

        $form = $this->page->find('css', '#questions');
        $this->fillForm(
            $form,
            array(
                'questions_OutWalletAnswer1_0' => true,
                'questions_OutWalletAnswer2_0' => true,
                'questions_OutWalletAnswer3_0' => true,
                'questions_OutWalletAnswer4_0' => true,
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");
        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('pidkiq.error.answers-help@renttrack.com', $errors[0]->getText());


        $this->fillForm(
            $form,
            array(
                'questions_OutWalletAnswer1_0' => true,
                'questions_OutWalletAnswer2_1' => true,
                'questions_OutWalletAnswer3_2' => true,
                'questions_OutWalletAnswer4_3' => true,
            )
        );
        $this->page->pressButton('pay_popup.step.next');


        $this->session->wait(
            $this->timeout,
            "jQuery('#checkout-payment-source:visible').length"
        );
        $payPopup->pressButton('pay_popup.step.previous');
        $this->session->wait(
            $this->timeout,
            "jQuery('#id-source-step:visible').length"
        );
        $this->page->clickLink('common.add_new');
        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true,
            )
        );

        $this->assertNotNull(
            $addresses = $form->findAll(
                'css',
                '#rentjeeves_checkoutbundle_paymentaccounttype_address_choice_box label span'
            )
        );
        $this->assertCount(3, $addresses);
        $this->assertEquals('New street, New city, NY 99999', $addresses[2]->getText());

        $this->assertNotNull($existPaymentSource = $this->page->findField('ko_unique_2'));
        $existPaymentSource->getParent()->click();

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout,
            "jQuery('#checkout-payment-source:visible').length"
        );


        $payPopup->pressButton('checkout.make_payment');

        $this->session->wait(
            $this->timeout,
            "false" // FIXME
        );

        $this->page->clickLink('tabs.summary');

        $this->session->wait(
            $this->timeout,
            "jQuery('#component-card-utilization-box:visible').length"
        );
        $this->assertNotNull($box = $this->page->find('css', '#component-card-utilization-box'));

        $this->logout();
    }
}
