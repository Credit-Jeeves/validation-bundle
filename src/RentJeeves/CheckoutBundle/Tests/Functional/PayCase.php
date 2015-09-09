<?php
namespace RentJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class PayCase extends BaseTestCase
{
    protected $paidForString;
    protected $payButtonName;

    public function setUp()
    {
        parent::setUp();
        $contractToSelect = 2;
        $tenantEmail = 'tenant11@example.com';
        $this->paidForString = $this->getPaidForDate($tenantEmail, $contractToSelect)->format('Y-m-d');
        $this->payButtonName = "contract-pay-" . ($contractToSelect);
    }

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

    private function getPaidForDate($tenantEmail, $contractIndex)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(array('email' => $tenantEmail));
        $contracts = array_reverse($tenant->getContracts()->toArray()); # contract buttons numbered from bottom up
        /**
         * @var $contract Contract
         */
        $contract = $contracts[$contractIndex - 1];
        $paidFor = new DateTime();
        $paidFor = $paidFor->setDate($paidFor->format('Y'), $paidFor->format('m'), $contract->getDueDate());

        return $paidFor;
    }

    /**
     * Turn off by Alexandr
     * @test
     */
    public function dayRange()
    {
        $this->markTestSkipped(
            "We have new code on the client side which don't
            allow set start_date wrong. And this test has wrong
            openDate and closeDate. New test for this constraint:
            src/RentJeeves/CheckoutBundle/Tests/Unit/DayRangeCase.php
            "
        );

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
        $this->page->pressButton($this->payButtonName);
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
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date' => $today->format('n/j/Y'),
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
     * @test
     */
    public function shouldForceUserToSetStartDate()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->updateGroupSettings(false);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($payButtons = $this->page->findAll('css', '.button-contract-pay'));
        $this->page->pressButton($this->payButtonName);

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        // recurring + don't choose day, year, month
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '100',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('payment.start_date.error.empty_date', $errors[0]->getText());

        // recurring + don't choose year
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '100',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
            )
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait($this->timeout, "$('#pay-popup>div.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('#pay-popup>div.overlay').is(':visible')");

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('payment.start_date.error.empty_date', $errors[0]->getText());

        // one_time and empty start_date
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
            )
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait($this->timeout, "$('#pay-popup>div.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('#pay-popup>div.overlay').is(':visible')");

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('payment.start_date.error.empty_date', $errors[0]->getText());

        // one_time and filled startDate ==> then payment in the past error
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date' => '1/1/2015',
            )
        );
        // is disabled datepicker?
        $this->assertNotNull($detailsDiv = $this->page->find('css', '.col'));
        $detailsDiv->click();
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");

        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "$('.overlay-trigger').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay-trigger').is(':visible')");

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(2, $errors);
        $this->assertEquals('payment.start_date.error.past', $errors[0]->getText());

        // correct case
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '100',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '20',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1,
            )
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );
        // everything set up fine: we are on the Source step
        $this->page->clickLink('payment.account.new');

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());
        $this->assertNotNull($closeButton = $payPopup->find('css', '.ui-dialog-titlebar-close'));
        $closeButton->click();
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

        $this->page->pressButton($this->payButtonName);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());

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
                    'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                    'rentjeeves_checkoutbundle_paymenttype_amount' => '0',
                    'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                    'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                    'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                    'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1
                )
            );
        }
        if ($infoMessage) {
            $this->fillForm(
                $form,
                array(
                    'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                    'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                    'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                )
            );
            $this->assertNotNull($informationBox = $payPopup->find('css', '.information-box'));
            $this->assertEquals('info.payment.date', $informationBox->getText());
        }

        if (!$payBalanceOnly) {
            $this->page->pressButton('pay_popup.step.next');
            $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");

            $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
            $this->assertCount(1, $errors);
            $this->assertEquals('checkout.error.total.min', $errors[0]->getText());

            $dueDate = cal_days_in_month(CAL_GREGORIAN, 2, date('Y'));
            if (!$infoMessage) {
                $this->fillForm(
                    $form,
                    array(
                        'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                        'rentjeeves_checkoutbundle_paymenttype_amount' => '1500',
                        'rentjeeves_checkoutbundle_paymenttype_dueDate' => $dueDate,
                    )
                );
            } else {
                $this->fillForm(
                    $form,
                    array(
                        'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
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
            $this->timeout + 85000, // local need more time for passed test
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
            $this->timeout,
            "jQuery('button:contains(pay_popup.close)').is(':visible')"
        );
        $payPopup->pressButton('pay_popup.close');

        $this->session->wait(
            $this->timeout + 10000,
            "!jQuery('#pay-popup:visible').length"
        );

        if ($summary) {
            $this->page->clickLink('tabs.summary');

            $this->session->wait(
                $this->timeout + 5000,
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
                    'total' => 1000.00,
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
        $tenantEmail = 'marion@rentrack.com';
        $contractToSelect = 2;
        $payButtonName = "contract-pay-" . $contractToSelect;
        $paidForString = $this->getPaidForDate($tenantEmail, $contractToSelect)->format('Y-m-d');

        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login($tenantEmail, 'pass');

        $this->page->pressButton($payButtonName);

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');
        $this->session->wait($this->timeout, "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length");
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31', // set date to 31 so we can always continue
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $paidForString
            )
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait($this->timeout, "jQuery('#id-source-step:visible').length");
        $this->page->find('css', '#id-source-step .payment-accounts label:nth-of-type(2)')->click();
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait($this->timeout, "jQuery('.pay-step:visible').length");
        $this->page->pressButton('checkout.make_payment');

        $this->session->wait(
            $this->timeout,
            "jQuery('button:contains(pay_popup.close)').is(':visible')"
        );
        $this->page->pressButton('pay_popup.close');

        $this->session->wait($this->timeout, "!jQuery('#pay-popup:visible').length");

        $this->logout();
    }

    protected function notSkipVerification($summary, $withUnanswered = false)
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
        $this->assertNotNull($form);

        if ($summary) {
            if ($withUnanswered) {
                // not set all fields
                $this->fillForm(
                    $form,
                    array(
                        'questions_OutWalletAnswer1_0' => true,
                        'questions_OutWalletAnswer2_1' => true,
                        'questions_OutWalletAnswer3_2' => true,
                    )
                );

                $this->page->pressButton('pay_popup.step.next');

                $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
                $this->assertEquals('pidkiq.error.unanswered_questions', $errors[0]->getText());

                return;
            }
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

    /**
     * @test
     */
    public function validateFieldOther()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->page->pressButton($this->payButtonName);
        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');
        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $dueDate = cal_days_in_month(CAL_GREGORIAN, 2, date('Y') + 1);
        //don't valid amountOther
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '100',
                'rentjeeves_checkoutbundle_paymenttype_amountOther' => -10,
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => $dueDate,
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1,
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");
        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('checkout.error.amountOther.min', $errors[0]->getText());

        // valid data
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '100',
                'rentjeeves_checkoutbundle_paymenttype_amountOther' => 10,
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => $dueDate,
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1,
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->assertNotNull($this->page->find('css', '.checkout-plus'));
    }

    /**
     * @test
     */
    public function shouldShowErrorIfThereAreUnansweredQuestions()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');

        $this->page->pressButton($this->payButtonName);

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $dueDate = cal_days_in_month(CAL_GREGORIAN, 2, date('Y'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1500',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => $dueDate,
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1
            )
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $this->page->clickLink('payment.account.new');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
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
            $this->timeout + 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );

        $this->notSkipVerification(true, true);
    }

    /**
     * @test
     */
    public function shouldShowRightAddress()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
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
    }

    /**
     * @test
     */
    public function shouldShowTypeCardForGroupWithActiveCardAndHideForGroupWithDisable()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);
        $this->assertFalse($group->isDisableCreditCard());

        $this->login('tenant11@example.com', 'pass');

        $this->page->pressButton('contract-pay-2');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');
        $dueDate = cal_days_in_month(CAL_GREGORIAN, 2, date('Y') + 1);
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => $dueDate,
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1,
            ]
        );

        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "$('#id-source-step').is(':visible')");
        $accounts = $this->page->findAll('css', 'div.payment-accounts label.radio');
        $this->assertCount(3, $accounts);

        $this->assertNotEmpty($newPaymentLink = $this->page->find('css', 'a.checkout-plus'));
        $newPaymentLink->click();

        $accountTypes = $this->page->findAll(
            'css',
            '#rentjeeves_checkoutbundle_paymentaccounttype_type_box label.radio'
        );
        $this->assertCount(2, $accountTypes);
        $cardType = $this->page->findAll('css', '#rentjeeves_checkoutbundle_paymentaccounttype_type_1');
        $this->assertNotNull($cardType);
        // disable "show card"
        $group->setDisableCreditCard(true);
        $this->getEntityManager()->flush($group);

        $this->session->reload();

        $this->page->pressButton('contract-pay-2');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => $dueDate,
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1,
            ]
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "$('#id-source-step').is(':visible')");

        $accounts = $this->page->findAll('css', 'div.payment-accounts label.radio');
        $this->assertCount(1, $accounts);

        $newPaymentLink = $this->page->find('css', 'a.checkout-plus');
        $newPaymentLink->click();

        // Hack (count returns 2 , 2 - not correct)
        $isHidden = $this->session->evaluateScript(
            'return $("#rentjeeves_checkoutbundle_paymentaccounttype_type_1").is(":hidden");'
        );

        $this->assertTrue($isHidden);
    }

    /**
     * @return array
     */
    public function dataForCheckPaymentProcessorLocker()
    {
        return [
            [true, 'alert.changing_payment_account'],
            [false, 'alert.tenant.verify_email']
        ];
    }

    /**
     * @test
     * @dataProvider dataForCheckPaymentProcessorLocker
     *
     * @param boolean $isPaymentProcessorLocked
     * @param string $alertMessage
     */
    public function checkPaymentProcessorLocker($isPaymentProcessorLocked, $alertMessage)
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding);
        $holding->setIsPaymentProcessorLocked($isPaymentProcessorLocked);
        $em->flush();
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($alert = $this->page->find('css', '.landlord-alert-text'));
        $this->assertEquals($alertMessage, $alert->getText());

        $this->page->pressButton($this->payButtonName);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '2000',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1
            ]
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $addNewAccount = $this->page->find('css', '.checkout-plus');
        $this->assertEquals($isPaymentProcessorLocked, empty($addNewAccount));
        $paymentSource = $this->page->find('css', '#rent-menu .last a');
        $this->assertNotEmpty($paymentSource);
        $paymentSource->click();
        $editSource = $this->page->find('css', '.edit');
        $delSource = $this->page->find('css', '.delete');

        $this->assertEquals($isPaymentProcessorLocked, empty($editSource));
        $this->assertEquals($isPaymentProcessorLocked, empty($delSource));
    }

    /**
     * @test
     */
    public function tryToCreate2ActivePayments()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $em = $this->getEntityManager();

        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');

        $this->assertNotEmpty($tenant, 'Check fixtures, should be present tenant with email tenant11@example.com');

        $tenant->setIsVerified(UserIsVerified::PASSED);

        $em->flush($tenant);

        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->find(9);

        $this->assertNotEmpty($contract, 'Check fixtures, should be present contract with id 9');

        $this->assertEmpty($contract->getActivePayment(), 'Check fixtures, contract should not have active payments');

        $this->login('tenant11@example.com', 'pass');

        $this->session->executeScript(sprintf('window.open("%s", "new_window")', $this->getUrl()));

        $this->session->switchToWindow('new_window');

        $this->page->pressButton($this->payButtonName);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '2000',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1
            ]
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $existingPaymentSource = $this->page->find(
            'css',
            '#id-source-step .payment-accounts label:nth-of-type(2)'
        );
        $this->assertNotNull($existingPaymentSource);
        $existingPaymentSource->click();

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#pay-popup div.pay-step:visible').length"
        );

        $this->page->pressButton('checkout.make_payment');

        $this->session->wait(
            $this->timeout,
            "jQuery('button:contains(pay_popup.close)').is(':visible')"
        );

        $em->refresh($contract);

        $this->assertNotEmpty($payment = $contract->getActivePayment(), 'Payment should be created for this contract');

        $this->assertEquals(2000, $payment->getAmount());

        $this->session->switchToWindow(); // switch to parent

        $this->page->pressButton($this->payButtonName);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '2001',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1
            ]
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $existingPaymentSource = $this->page->find(
            'css',
            '#id-source-step .payment-accounts label:nth-of-type(2)'
        );
        $this->assertNotNull($existingPaymentSource);
        $existingPaymentSource->click();

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#pay-popup div.pay-step:visible').length"
        );

        $this->page->pressButton('checkout.make_payment');

        $this->session->wait(
            $this->timeout,
            "jQuery('button:contains(pay_popup.close)').is(':visible')"
        );

        $em->clear();

        $contract = $em->getRepository('RjDataBundle:Contract')->find(9);

        // getActivePayment method can throw Exception if we have more then 1 active payment for contract
        // so should check manually count of it
        $payments = $contract->getPayments()->filter(
            function (Payment $payment) {
                if (PaymentStatus::ACTIVE == $payment->getStatus()) {
                    return true;
                }

                return false;
            }
        );

        $this->assertCount(1, $payments, 'Should not be created duplicate payment for contract');

        $em->refresh($payments->first());

        $this->assertEquals(2001, $payments->first()->getAmount(), 'Active Payment should be updated');
    }
}
