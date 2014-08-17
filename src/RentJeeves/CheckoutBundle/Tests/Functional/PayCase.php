<?php
namespace RentJeeves\CheckoutBundle\Tests\Functional;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class PayCase extends BaseTestCase
{
    public function provider()
    {
        return array(
            array($summary = true, $skipVerification = false, $infoMessage = false, $payBalanceOnly = false),
            array($summary = false, $skipVerification = false, $infoMessage = false, $payBalanceOnly = false),
            array($summary = null, $skipVerification = true, $infoMessage = true, $payBalanceOnly = false),
            array($summary = null, $skipVerification = true, $infoMessage = false, $payBalanceOnly = true),
        );
    }

    protected function updateGroupSettings($payBalanceOnly)
    {
        self::$kernel = null;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(array('email' => 'tenant11@example.com'));
        $contracts = $tenant->getContracts();
        /**
         * @var $contract Contract
         */
        foreach ($contracts as $contract) {
            $group = $contract->getGroup();
            $groupSetting = $group->getGroupSettings();
            $groupSetting->setIsPidVerificationSkipped(true);
            if ($payBalanceOnly) {
                $groupSetting->setPayBalanceOnly($payBalanceOnly);
                $groupSetting->setIsIntegrated(true);
                $contract->setIntegratedBalance(1000);
                $em->persist($contract);
            }
            $em->persist($groupSetting);
        }
        $em->flush();
    }

    /**
     * @test
     */
    public function dayRange()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        self::$kernel = null;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(array('email' => 'tenant11@example.com'));
        $contracts = $tenant->getContracts();
        $today = new DateTime();
        $today->modify("-1 day");
        /**
         * @var $contract Contract
         */
        foreach ($contracts as $contract) {
            $group = $contract->getGroup();
            $groupSetting = $group->getGroupSettings();
            $groupSetting->setOpenDate($today->format('j'));
            $groupSetting->setCloseDate($today->format('j'));
            $em->persist($groupSetting);
        }
        $em->flush();
        $this->login('tenant11@example.com', 'pass');
        $this->page->pressButton('contract-pay-2');
        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $today = new DateTime();
        $today->modify('+5 day');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_type'      => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date'=> $today->format('n/j/Y'),
            )
        );

        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('payment_form.start_date.error_range', $errors[0]->getText());
        $this->logout();
    }

    /**
     * @dataProvider provider
     * @test
     */
    public function recurring($summary, $skipVerification, $infoMessage, $payBalanceOnly)
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        if ($skipVerification) {
            $this->updateGroupSettings($payBalanceOnly);
        }
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($payButtons = $this->page->findAll('css', '.button-contract-pay'));
        $this->assertCount(4, $payButtons, 'Wrong number of contracts');
        $payButtons[2]->click();
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

        if ($payBalanceOnly) {
            $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentbalanceonlytype');
            $startDate = new DateTime();
            $startDate->modify('+1 month');
            $this->fillForm(
                $form,
                array(
                    'rentjeeves_checkoutbundle_paymentbalanceonlytype_start_date' => $startDate->format('m/d/Y'),
                )
            );
        } else {
            $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

            $this->session->wait(
                $this->timeout,
                "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
            );
            $this->fillForm(
                $form,
                array(
                    'rentjeeves_checkoutbundle_paymenttype_amount' => '0',
                    'rentjeeves_checkoutbundle_paymenttype_type'    => PaymentTypeEnum::RECURRING,
                    'rentjeeves_checkoutbundle_paymenttype_dueDate'     => '31',
                    'rentjeeves_checkoutbundle_paymenttype_startMonth'  => 2,
                    'rentjeeves_checkoutbundle_paymenttype_startYear'   => date('Y')+1
                )
            );
        }
        if ($infoMessage) {
            $this->fillForm(
                $form,
                array(
                    'rentjeeves_checkoutbundle_paymenttype_startMonth'  => 2,
                    'rentjeeves_checkoutbundle_paymenttype_dueDate'     => '31',
                )
            );
            $this->assertNotNull($informationBox = $payPopup->find('css', '.information-box'));
            $this->assertEquals('info.payment.date', $informationBox->getText());
        }

        if (!$payBalanceOnly) {
            $this->page->pressButton('pay_popup.step.next');
            $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");

            $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
            $this->assertCount(2, $errors);
            $this->assertEquals('checkout.error.amount.min', $errors[1]->getText());

            if (!$infoMessage) {
                $dueDate = cal_days_in_month(CAL_GREGORIAN, date('n'), date('Y'));
                $this->fillForm(
                    $form,
                    array(
                        'rentjeeves_checkoutbundle_paymenttype_amount' => '1500',
                        'rentjeeves_checkoutbundle_paymenttype_dueDate' => $dueDate,
                    )
                );
            } else {
                $this->fillForm(
                    $form,
                    array(
                        'rentjeeves_checkoutbundle_paymenttype_amount' => '1500',
                    )
                );
            }
        }

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $this->page->clickLink('payment.account.new');

        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");
        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(5, $errors);

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');

        /*
         * Test for not match repeated value for Account Number
         */
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test payment',
                'rentjeeves_checkoutbundle_paymentaccounttype_PayorName' => 'Timothy APPLEGATE',
                'rentjeeves_checkoutbundle_paymentaccounttype_RoutingNumber' => '062202574',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumber' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumberAgain' => '123245687',
                'rentjeeves_checkoutbundle_paymentaccounttype_ACHDepositType_0' => true,
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "(jQuery('#pay-popup .attention-box li').length < 5)");
        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('checkout.error.account_number.match', $errors[0]->getHtml());

        /*
         * Continue test
         */
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test payment',
                'rentjeeves_checkoutbundle_paymentaccounttype_PayorName' => 'Timothy APPLEGATE',
                'rentjeeves_checkoutbundle_paymentaccounttype_RoutingNumber' => '062202574',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumber' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumberAgain' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_ACHDepositType_0' => true,
            )
        );


        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout+ 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );

        if (!$skipVerification) {
            $this->notSkipVerification($summary);
        }

        if ($infoMessage) {
            $this->assertNotNull($informationBox = $payPopup->find('css', '.information-box'));
            $this->assertEquals('info.payment.date', $informationBox->getText());
        }

        $this->session->wait(
            $this->timeout,
            "jQuery('button:contains(checkout.make_payment)').is(':visible')"
        );
        $payPopup->pressButton('checkout.make_payment');

        $this->session->wait(
            $this->timeout + 10000,
            "!jQuery('#pay-popup:visible').length"
        );

        if ($summary) {
            $this->page->clickLink('tabs.summary');

            $this->session->wait(
                $this->timeout+5000,
                "jQuery('#component-card-utilization-box:visible').length"
            );
            $this->assertNotNull($box = $this->page->find('css', '#component-card-utilization-box'));
        } else {
            $this->assertNotNull($pay = $this->page->find('css', '#pay-popup'));
            $this->assertFalse($pay->isVisible());
        }

        if ($payBalanceOnly) {
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $payment = $em->getRepository('RjDataBundle:Payment')->findBy(
                array(
                    'type' => PaymentType::ONE_TIME,
                    'total'=> 1000.00,
                )
            );
            $this->assertEquals(1, count($payment));
        }

        $this->logout();
    }

    /**
     * Choose an existing payment account that is registered to another deposit
     * account to make sure we can register the old token to a new merchant.
     *
     * @test
     */
    public function registerAccountToAdditionalMerchant()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('marion@rentrack.com', 'pass');

        $this->page->pressButton('contract-pay-2');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait($this->timeout, "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length");

        // set date to 31 so we can always continue
        $form->fillField('rentjeeves_checkoutbundle_paymenttype_dueDate', '31');
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait($this->timeout, "jQuery('#id-source-step:visible').length");
        $this->page->find('css', '#id-source-step .payment-accounts label:nth-of-type(2)')->click();
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait($this->timeout, "jQuery('.pay-step:visible').length");
        $this->page->pressButton('checkout.make_payment');

        $this->session->wait($this->timeout, "!jQuery('#pay-popup:visible').length");

        $this->logout();
    }

    protected function notSkipVerification($summary)
    {
        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());
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
                'rentjeeves_checkoutbundle_userdetailstype_new_address_street' => 'Street with wrong symbols @#$%',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_city' => 'City with wrong symbols :"|',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_zip' => '65487',
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length == 2");
        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(2, $errors);
        $this->assertEquals('error.user.street.invalid', $errors[0]->getText());
        $this->assertEquals('error.user.city.invalid', $errors[1]->getText());


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

        if ($summary) {
            //Fill correct answer
            $this->fillForm(
                $form,
                array(
                    'questions_OutWalletAnswer1_0' => true,
                    'questions_OutWalletAnswer2_1' => true,
                    'questions_OutWalletAnswer3_2' => true,
                    'questions_OutWalletAnswer4_3' => true,
                )
            );
        } else {
            //Fill incorrect answer
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
            $this->assertEquals('pidkiq.error.incorrect.answer-help@renttrack.com', $errors[0]->getText());
        }
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
        $this->page->clickLink('payment.account.new');
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

        $existingPaymentSource = $this->page->find(
            'css',
            '#id-source-step .payment-accounts label:nth-of-type(2)'
        );
        $this->assertNotNull($existingPaymentSource);
        $existingPaymentSource->click();

        $this->page->pressButton('pay_popup.step.next');
    }

    /**
     * @test
     */
    public function oneTimePayment()
    {
        $this->markTestIncomplete('FINISH');
    }
}
