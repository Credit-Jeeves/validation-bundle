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
     * @test
     */
    public function shouldCreateRecurringPaymentAndGoThrowVerification()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');

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
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '0',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1
            )
        );

        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "jQuery('#pay-popup .attention-box li').length");

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-popup .attention-box li'));
        $this->assertCount(1, $errors);
        $this->assertEquals('checkout.error.total.min', $errors[0]->getText());

        $dueDate = cal_days_in_month(CAL_GREGORIAN, 2, date('Y'));

        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1500',
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => $dueDate,
            )
        );

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

        $this->notSkipVerification(true);

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

        $this->page->clickLink('tabs.summary');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#component-card-utilization-box:visible').length"
        );
        $this->assertNotNull($box = $this->page->find('css', '#component-card-utilization-box'));
        $this->logout();
    }

    /**
     * @test
     */
    public function shouldCreatePaymentIfSkipVerificationIsTrue()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $payment = $this->getEntityManager()->getRepository('RjDataBundle:Payment')->findBy(
            [
                'type' => PaymentType::RECURRING,
                'total' => 999.00,
            ]
        );
        $this->assertCount(0, $payment, 'Should exist 0 payments for given params');
        $this->updateGroupSettings(false);
        $this->login('tenant11@example.com', 'pass');
        $this->page->pressButton($this->payButtonName);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'), 'PayPopup should exist');
        $this->assertNotNull($payPopup = $payPopup->getParent(), 'PayPopup->getParent() should exist');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '999',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 2,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1
            )
        );

        $this->assertNotNull($informationBox = $payPopup->find('css', '.information-box'));
        $this->assertEquals('info.payment.date', $informationBox->getText());

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
            $this->timeout + 10000,
            "!jQuery('#id-source-step').is(':visible')"
        );

        $this->assertNotNull($informationBox = $payPopup->find('css', '.information-box'));
        $this->assertEquals('info.payment.date', $informationBox->getText());

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

        $this->getEntityManager()->clear();
        $payment = $this->getEntityManager()->getRepository('RjDataBundle:Payment')->findBy(
            [
                'type' => PaymentType::RECURRING,
                'total' => 999.00,
            ]
        );
        $this->assertCount(1, $payment, 'Should exist 1 payment for given params');
        $this->logout();
    }

    /**
     * @test
     */
    public function shouldCreateOneTimePaymentWithPayBalanceOnly()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $em = $this->getEntityManager();
        $payment = $em->getRepository('RjDataBundle:Payment')->findBy(
            [
                'type' => PaymentType::ONE_TIME,
                'total' => 1000.00,
            ]
        );
        $this->assertCount(0, $payment, 'Should exist 0 payments for given params');

        $this->updateGroupSettings(true);

        $this->login('tenant11@example.com', 'pass');

        $this->page->pressButton($this->payButtonName);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'), 'PayPopup should exist');
        $this->assertNotNull($payPopup = $payPopup->getParent(), 'PayPopup->getParent() should exist');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentbalanceonlytype');
        $startDate = new DateTime();
        $startDate->modify('+1 month');
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymentbalanceonlytype_start_date' => $startDate->format('m/d/Y'),
            ]
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
            [
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test payment',
                'rentjeeves_checkoutbundle_paymentaccounttype_PayorName' => 'Timothy APPLEGATE',
                'rentjeeves_checkoutbundle_paymentaccounttype_RoutingNumber' => '062202574',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumber' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumberAgain' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_ACHDepositType_0' => true,
            ]
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "!jQuery('#id-source-step').is(':visible')"
        );

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

        $this->assertNotNull($pay = $this->page->find('css', '#pay-popup'), 'PaymentWizard should not be NULL');
        $this->assertFalse($pay->isVisible(), 'PaymentWizard should not be visible');

        $em = $this->getEntityManager();
        $payment = $em->getRepository('RjDataBundle:Payment')->findBy(
            [
                'type' => PaymentType::ONE_TIME,
                'total' => 1000.00,
            ]
        );
        $this->assertCount(1, $payment, 'Should exist one payment for given params');
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
        $this->assertEquals('770 Broadway, #2-a New York, NY 10003 *required', $propertyAddress->getText());
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
        $this->assertCount(3, $accountTypes);
        $this->assertFalse($accountTypes[2]->isVisible(), 'DebitCard type should not be visible');
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
    public function shouldDisableTodayOnDatepickerAfterCutoffTime()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $this->login('tenant11@example.com', 'pass');

        $this->page->pressButton($this->payButtonName);

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '2000',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date' => ''
            ]
        );

        $this->assertNotNull($this->page->find('css', '#ui-datepicker-div'), 'Datepicker not found');

        $this->assertNotNull(
            $this->page->find(
                'css',
                '#ui-datepicker-div td.ui-datepicker-unselectable.ui-state-disabled.ui-datepicker-today'
            ),
            'Today should be disabled'
        );
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

        $this->assertEmpty(
            $contract->getActiveRentPayment(),
            'Check fixtures, contract should not have active payments'
        );

        $this->login('tenant11@example.com', 'pass');

        $this->session->executeScript(sprintf('window.open("%s", "new_window")', $this->getUrl()));

        $this->session->switchToWindow('new_window');

        $this->page->pressButton($this->payButtonName);

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

        $this->assertNotEmpty(
            $payment = $contract->getActiveRentPayment(),
            'Payment should be created for this contract'
        );

        $this->assertEquals(2000, $payment->getAmount());

        $this->session->switchToWindow(); // switch to parent

        $this->page->pressButton($this->payButtonName);

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

    /**
     * @test
     */
    public function shouldHideAndShowRentOnDashboardWhenChangeGroupSettingsOption()
    {
        $this->load(true);
        /** @var Tenant $tenant */
        $tenant = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');
        $this->assertNotNull($tenant, 'Check fixtures, tenant with email "tenant11@example.com" should be present');
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 2);
        $this->assertNotNull($contract, 'Check fixtures, contract with id 2 should be present');
        $this->assertEquals(
            $contract->getTenant()->getId(),
            $tenant->getId(),
            'Check fixtures, contract with id 2 should belong to tenant with email "tenant11@example.com"'
        );
        $contract->getGroupSettings()->setShowRentOnDashboard(false);
        $this->getEntityManager()->flush($contract->getGroupSettings());

        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');

        $rowsSelector = '#current-payments table.properties-table>tbody>tr';
        $tableRows = $this->getDomElements($rowsSelector);
        $this->assertGreaterThan(1, count($tableRows), 'Table should contains more then 1 contract');
        $this->assertNotNull(
            $rentColumn = $tableRows[1]->find('css', 'td'),
            'Second row should contains at list 1 column'
        );
        $this->assertEquals('rent.not_shown', $rentColumn->getText(), 'Rent column should contains "rent.not_shown"');

        $contract->getGroupSettings()->setShowRentOnDashboard(true);
        $this->getEntityManager()->flush($contract->getGroupSettings());
        $this->session->reload();

        $rowsSelector = '#current-payments table.properties-table>tbody>tr';
        $tableRows = $this->getDomElements($rowsSelector);
        $this->assertGreaterThan(1, count($tableRows), 'Table should contains more then 1 contract');
        $this->assertNotNull(
            $rentColumn = $tableRows[1]->find('css', 'td'),
            'Second row should contains at list 1 column'
        );
        $this->assertEquals(
            '$' . $contract->getRent(),
            $rentColumn->getText(),
            'Rent column should contains rent'
        );
    }

    /**
     * @test
     */
    public function shouldHideAndShowRentOnWizardWhenChangeGroupSettingsOption()
    {
        $this->load(true);
        /** @var Tenant $tenant */
        $tenant = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');
        $this->assertNotNull($tenant, 'Check fixtures, tenant with email "tenant11@example.com" should be present');
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 9);
        $this->assertNotNull($contract, 'Check fixtures, contract with id 9 should be present');
        $contract->getGroupSettings()->setShowRentOnWizard(false);
        $this->getEntityManager()->flush($contract->getGroupSettings());

        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');

        $btnSelector = sprintf('button[data-bind="click: openPayPopup.bind($data, %d)"]', $contract->getId());
        $payBtn = $this->getDomElement($btnSelector);
        $payBtn->click();
        $amountField = $this->getDomElement('#rentjeeves_checkoutbundle_paymenttype_amount');

        $this->assertEmpty($amountField->getValue(), 'Rent field should be empty');

        $contract->getGroupSettings()->setShowRentOnWizard(true);
        $this->getEntityManager()->flush($contract->getGroupSettings());
        $this->session->reload();
        $payBtn->click();

        $amountField = $this->getDomElement('#rentjeeves_checkoutbundle_paymenttype_amount');

        $this->assertEquals(
            $contract->getRent(),
            $amountField->getValue(),
            'Rent field should equals rent from contract'
        );
    }
}
