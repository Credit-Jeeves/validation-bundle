<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use ACI\Utils\OldProfilesStorage;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Payum\AciCollectPay\Model\Profile;
use Payum\AciCollectPay\Request\ProfileRequest\DeleteProfile;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\BinlistBank;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\DebitType;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\ExternalApiBundle\Services\Binlist\BinlistCard;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use Symfony\Component\Config\FileLocator;

class AciCollectPayCase extends BaseTestCase
{
    use OldProfilesStorage;

    /**
     * @var string
     */
    protected $paidForStringForCreate;
    /**
     * @var string
     */
    protected $paidForStringForUpdate;
    /**
     * @var string
     */
    protected $payButtonNameForCreate;
    /**
     * @var string
     */
    protected $payButtonNameForUpdate;
    /**
     * @var FileLocator
     */
    protected $fixtureLocator;
    /**
     * @var Contract
     */
    protected $contractForCreate;

    /**
     * @var Contract
     */
    protected $contractForUpdate;

    public function setUp()
    {
        parent::setUp();

        $this->load(true);

        $this->fixtureLocator = new FileLocator(
            [__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures']
        );

        $contractToSelectForCreate = 2;
        $contractToSelectForUpdate = 1;
        $tenantEmail = 'tenant11@example.com';
        $this->contractForCreate = $this->getContract($tenantEmail, $contractToSelectForCreate);
        $this->contractForUpdate = $this->getContract($tenantEmail, $contractToSelectForUpdate);
        if ($profileId = $this->getOldProfileId(md5($this->contractForCreate->getTenant()->getId()))) {
            $this->deleteProfile($profileId);
        }

        $this->contractForCreate->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);

        $depositAccount = new DepositAccount($this->contractForCreate->getGroup());
        $depositAccount->setPaymentProcessor(
            $this->contractForCreate->getGroup()->getGroupSettings()->getPaymentProcessor()
        );
        $depositAccount->setType(DepositAccountType::RENT);
        $depositAccount->setMerchantName(564075);
        $depositAccount->setHolding($this->contractForCreate->getGroup()->getHolding());

        $this->contractForCreate->getGroup()->addDepositAccount($depositAccount);

        $this->getEntityManager()->persist($this->contractForCreate->getGroup());

        $this->getEntityManager()->flush();

        $this->paidForStringForCreate = $this->getPaidForDate($this->contractForCreate)->format('Y-m-d');
        $this->paidForStringForUpdate = $this->getPaidForDate($this->contractForUpdate)->format('Y-m-d');
        $this->payButtonNameForCreate = "contract-pay-" . ($contractToSelectForCreate);
        $this->payButtonNameForUpdate = "contract-pay-". ($contractToSelectForUpdate);
    }

    /**
     * @return array
     */
    public function createAccountDataProvider()
    {
        return [
            [PaymentAccountType::BANK],
            [PaymentAccountType::CARD]
        ];
    }

    /**
     * @test
     * @dataProvider createAccountDataProvider
     */
    public function createAccount($type)
    {
        $this->setDefaultSession('selenium2');
        $repo = $this->getEntityManager()->getRepository('RjDataBundle:PaymentAccount');

        $countsBefore = count($repo->findBy(['paymentProcessor' => PaymentProcessor::ACI]));
        $repo->clear();

        $this->login('tenant11@example.com', 'pass');

        $this->page->pressButton($this->payButtonNameForCreate);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1000',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date' => (new \DateTime('+2 day'))->format('n/j/Y'),
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForStringForCreate,
            ]
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');

        $this->fillForm($form, $this->getPaymentAccountFormData($type));

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );

        $this->getEntityManager()->refresh($this->contractForCreate);
        $this->getEntityManager()->refresh($this->contractForCreate->getTenant());

        $this->assertNotEmpty($profile = $this->contractForCreate->getTenant()->getAciCollectPayProfile());

        $merchantName = $this->contractForCreate
            ->getGroup()->getRentDepositAccountForCurrentPaymentProcessor()->getMerchantName();

        $this->assertTrue(
            $profile->hasBillingAccountForDivisionId($merchantName),
            'Profile should have billing account'
        );

        $countsAfter = count($repo->findBy(['paymentProcessor' => PaymentProcessor::ACI]));
        $this->assertEquals($countsBefore + 1, $countsAfter);
        $this->assertNotEmpty($closeButton = $this->page->find('css', '.ui-dialog-titlebar .ui-button'));
        $closeButton->click();
        //Check update
        $this->page->pressButton($this->payButtonNameForUpdate);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());
        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );

        $this->assertNotEmpty($forms = $this->page->findAll('css', 'form'));
        $this->assertCount(4, $forms);

        $forms[0]->find('css', 'select[name="rentjeeves_checkoutbundle_paymenttype[type]"]')->selectOption(
            PaymentTypeEnum::ONE_TIME
        );
        $forms[0]->find('css', 'select[name="rentjeeves_checkoutbundle_paymenttype[paidFor]"]')->setValue(
            $this->paidForStringForUpdate
        );
        $forms[0]->find('css', 'input[name="rentjeeves_checkoutbundle_paymenttype[amount]"]')->setValue('1000');
        $forms[0]->find('css', 'input[name="rentjeeves_checkoutbundle_paymenttype[start_date]"]')->setValue(
            (new \DateTime('+2 day'))->format('n/j/Y')
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $this->assertNotEmpty($radioButton = $this->page->find('css', '.ui-dialog .payment-accounts .radio input'));
        $radioButton->getParent()->click();
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait(
            $this->timeout + 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );

        $this->getEntityManager()->refresh($this->contractForUpdate);
        $this->assertNotEmpty($this->contractForUpdate->getTenant()->getAciCollectPayProfileId());

        $this->setOldProfileId(
            md5($this->contractForCreate->getTenant()->getId()),
            $this->contractForCreate->getTenant()->getAciCollectPayProfileId()
        );

        $this->deleteProfile($this->contractForCreate->getTenant()->getAciCollectPayProfileId());
    }

    /**
     * @param $type
     * @return array
     */
    private function getPaymentAccountFormData($type)
    {
        switch ($type) {
            case PaymentAccountType::BANK:
                return [
                    'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test aci Bank',
                    'rentjeeves_checkoutbundle_paymentaccounttype_PayorName' => 'Timothy APPLEGATE',
                    'rentjeeves_checkoutbundle_paymentaccounttype_RoutingNumber' => '062202574',
                    'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumber' => '123245678',
                    'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber_AccountNumberAgain' => '123245678',
                    'rentjeeves_checkoutbundle_paymentaccounttype_ACHDepositType_0' => true,
                ];
            case PaymentAccountType::CARD:
            case PaymentAccountType::DEBIT_CARD:
                return [
                    'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true,
                    'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test aci Card',
                    'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy APPLEGATE',
                    'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5110200200001115',
                    'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '123',
                    'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => '12',
                    'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => '2025',
                    'rentjeeves_checkoutbundle_paymentaccounttype_address_choice_53' => true,
                ];
        }

    }

    /**
     * @param $tenantEmail
     * @param $contractIndex
     * @return Contract
     */
    private function getContract($tenantEmail, $contractIndex)
    {
        /**
         * @var $tenant Tenant
         */
        $tenant = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->findOneBy(['email' => $tenantEmail]);
        $contracts = array_reverse($tenant->getContracts()->toArray()); # contract buttons numbered from bottom up

        return $contracts[$contractIndex - 1];
    }

    /**
     * @param  Contract  $contract
     * @return \DateTime
     */
    private function getPaidForDate(Contract $contract)
    {
        $paidFor = new \DateTime();
        $paidFor = $paidFor->setDate($paidFor->format('Y'), $paidFor->format('m'), $contract->getDueDate());

        return $paidFor;
    }

    /**
     * @param int $profileId
     */
    protected function deleteProfile($profileId)
    {
        $profile = new Profile();

        $profile->setProfileId($profileId);

        $request = new DeleteProfile($profile);

        $this->getContainer()->get('payum')->getPayment('aci_collect_pay')->execute($request);

        $this->assertTrue($request->getIsSuccessful());

        $this->unsetOldProfileId($profileId);
    }

    /**
     * @test
     */
    public function shouldCheckValidationCardAndCVSOnPaymentSource()
    {
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->page->pressButton($this->payButtonNameForCreate);

        $this->assertNotNull($payPopup = $this->page->find('css', '#pay-popup'));
        $this->assertNotNull($payPopup = $payPopup->getParent());

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');
        $startDate = new \DateTime();
        $startDate->modify('+1 day');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForStringForCreate,
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date' => $startDate->format('n/j/Y'),
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1000',
            ]
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true,
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test aci Card',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy APPLEGATE',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '511020020000111588',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '12H',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => '12',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => '2025',
                'rentjeeves_checkoutbundle_paymentaccounttype_address_choice_53' => true,
            ]
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout,
            "!jQuery('#id-source-step').is(':visible')"
        );

        $this->assertNotEmpty($errors = $this->page->findAll('css', '.attention-box li'));
        $this->assertCount(2, $errors);
        $this->assertEquals('Unsupported card type or invalid card number.', $errors[0]->getText());
        $this->assertEquals('checkout.error.csc.type', $errors[1]->getText());

    }

    /**
     * @test
     */
    public function shouldCreateDebitCardPaymentAccountDebitTypeDebit()
    {
        // prepare fixtures
        $em = $this->getEntityManager();
        $group = $em->getRepository('DataBundle:Group')->find(24);
        $this->assertNotNull($group, 'Check fixtures, group with id 24 should exist');
        $group->getGroupSettings()->setAllowedDebitFee(true);
        $group->getGroupSettings()->setDebitFee(2);
        $em->flush();
        $repo = $this->getEntityManager()->getRepository('RjDataBundle:PaymentAccount');
        $countsBefore = count($repo->findBy(['paymentProcessor' => PaymentProcessor::ACI]));
        $repo->clear();

        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');

        $this->page->pressButton($this->payButtonNameForCreate);

        $this->getDomElement('#pay-popup');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1000',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date' => (new \DateTime('+2 day'))->format('n/j/Y'),
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForStringForCreate,
            ]
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');

        $formData = $this->getPaymentAccountFormData(PaymentAccountType::DEBIT_CARD);

        $formData['rentjeeves_checkoutbundle_paymentaccounttype_type_2'] = true;
        $formData['rentjeeves_checkoutbundle_paymentaccounttype_CardNumber'] = '5113298820090135';

        $this->fillForm($form, $formData);

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );

        $this->getEntityManager()->refresh($this->contractForCreate);
        $this->getEntityManager()->refresh($this->contractForCreate->getTenant());

        $this->assertNotEmpty($profile = $this->contractForCreate->getTenant()->getAciCollectPayProfile());

        $merchantName = $this->contractForCreate
            ->getGroup()->getRentDepositAccountForCurrentPaymentProcessor()->getMerchantName();

        $this->assertTrue(
            $profile->hasBillingAccountForDivisionId($merchantName),
            'Profile should have billing account'
        );
        $aciPaymentAccounts = $repo->findBy(['paymentProcessor' => PaymentProcessor::ACI]);
        $this->assertCount($countsBefore + 1, $aciPaymentAccounts);
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = end($aciPaymentAccounts);
        $this->assertEquals(
            PaymentAccountType::DEBIT_CARD,
            $paymentAccount->getType(),
            'Created Payment Account should be Debit Card'
        );
        $this->assertEquals(
            DebitType::DEBIT,
            $paymentAccount->getDebitType(),
            'Created Payment Account should be have debit_type "debit"'
        );
        $this->setOldProfileId(
            md5($this->contractForCreate->getTenant()->getId()),
            $this->contractForCreate->getTenant()->getAciCollectPayProfileId()
        );
        $this->deleteProfile($this->contractForCreate->getTenant()->getAciCollectPayProfileId());
    }

    /**
     * @test
     */
    public function shouldCreateDebitCardPaymentAccountDebitTypeSignatureNonExempt()
    {
        // prepare fixtures
        $em = $this->getEntityManager();
        $group = $em->getRepository('DataBundle:Group')->find(24);
        $this->assertNotNull($group, 'Check fixtures, group with id 24 should exist');
        $group->getGroupSettings()->setAllowedDebitFee(true);
        $group->getGroupSettings()->setDebitFee(2);
        $binlistBank = new BinlistBank();
        $binlistBank->setBankName('ABC');
        $binlistBank->setLowDebitFee(true);
        $binlistCard = new DebitCardBinlist();
        $binlistCard->setBinlistBank($binlistBank);
        $binlistCard->setIin('511020');
        $binlistCard->setCardType(BinlistCard::TYPE_DEBIT);
        $em->persist($binlistCard);

        $em->flush();
        $repo = $this->getEntityManager()->getRepository('RjDataBundle:PaymentAccount');
        $countsBefore = count($repo->findBy(['paymentProcessor' => PaymentProcessor::ACI]));
        $repo->clear();

        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');

        $this->page->pressButton($this->payButtonNameForCreate);

        $this->getDomElement('#pay-popup');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymenttype');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1000',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date' => (new \DateTime('+2 day'))->format('n/j/Y'),
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForStringForCreate,
            ]
        );

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');

        $formData = $this->getPaymentAccountFormData(PaymentAccountType::DEBIT_CARD);

        $formData['rentjeeves_checkoutbundle_paymentaccounttype_type_2'] = true;

        $this->fillForm($form, $formData);

        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );

        $this->getEntityManager()->refresh($this->contractForCreate);
        $this->getEntityManager()->refresh($this->contractForCreate->getTenant());

        $this->assertNotEmpty(
            $profile = $this->contractForCreate->getTenant()->getAciCollectPayProfile(),
            'Profile should be created'
        );

        $merchantName = $this->contractForCreate
            ->getGroup()->getRentDepositAccountForCurrentPaymentProcessor()->getMerchantName();

        $this->assertTrue(
            $profile->hasBillingAccountForDivisionId($merchantName),
            'Profile should have billing account'
        );
        $aciPaymentAccounts = $repo->findBy(['paymentProcessor' => PaymentProcessor::ACI]);
        $this->assertCount($countsBefore + 1, $aciPaymentAccounts, 'Should be created new Payment Account');
        /** @var PaymentAccount $paymentAccount */
        $paymentAccount = end($aciPaymentAccounts);
        $this->assertEquals(
            PaymentAccountType::DEBIT_CARD,
            $paymentAccount->getType(),
            'Created Payment Account should be Debit Card'
        );
        $this->assertEquals(
            DebitType::SIGNATURE_NON_EXEMPT,
            $paymentAccount->getDebitType(),
            'Created Payment Account should be have debit_type "signature_non_exempt"'
        );
        $this->setOldProfileId(
            md5($this->contractForCreate->getTenant()->getId()),
            $this->contractForCreate->getTenant()->getAciCollectPayProfileId()
        );
        $this->deleteProfile($this->contractForCreate->getTenant()->getAciCollectPayProfileId());
    }

    /**
     * @test
     */
    public function shouldSetPaymentToFlaggedWhenTenantSetsAmountGreaterThanMaxLimit()
    {
        // prepare fixtures
        $em = $this->getEntityManager();
        $group = $em->getRepository('DataBundle:Group')->find(24);
        $this->assertNotNull($group, 'Check fixtures, group with id 24 should exist');
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        $this->assertNotEmpty($tenant, 'tenant11@example.com not found');
        $tenant->setIsVerified(UserIsVerified::PASSED);
        $em->flush();
        $this->assertCount(6, $em->getRepository('RjDataBundle:Payment')->findAll(), 'Expected 6 payments in fixtures');

        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->page->pressButton($this->payButtonNameForCreate);
        $this->getDomElement('#pay-popup');
        $form = $this->getDomElement('#rentjeeves_checkoutbundle_paymenttype');

        $this->session->wait(
            $this->timeout,
            "jQuery('#rentjeeves_checkoutbundle_paymenttype_amount:visible').length"
        );
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForStringForCreate,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '10999',
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::RECURRING,
                'rentjeeves_checkoutbundle_paymenttype_dueDate' => '31',
                'rentjeeves_checkoutbundle_paymenttype_startMonth' => 3,
                'rentjeeves_checkoutbundle_paymenttype_startYear' => date('Y') + 1
            ]
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#id-source-step:visible').length"
        );

        $form = $this->getDomElement('#rentjeeves_checkoutbundle_paymentaccounttype');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_paymentaccounttype_type_1' => true,
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test aci Card',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy APPLEGATE',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5110200200001115',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '123',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => '12',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => '2025',
                'rentjeeves_checkoutbundle_paymentaccounttype_address_choice_53' => true,
            ]
        );
        $this->page->pressButton('pay_popup.step.next');


        $this->session->wait($this->timeout, "jQuery('.pay-step:visible').length");

        $em->refresh($this->contractForCreate->getTenant());
        $this->setOldProfileId(
            md5($this->contractForCreate->getTenant()->getId()),
            $this->contractForCreate->getTenant()->getAciCollectPayProfileId()
        );

        $this->page->pressButton('checkout.make_payment');

        $this->session->wait(
            $this->timeout,
            "jQuery('button:contains(pay_popup.close)').is(':visible')"
        );
        $this->page->pressButton('pay_popup.close');

        $em->clear();
        $this->assertCount(7, $em->getRepository('RjDataBundle:Payment')->findAll(), 'Expected 6 payments in fixtures');
        $this->assertNotNull($payment = $em->getRepository('RjDataBundle:Payment')->find(7), 'Payment #7 should exist');

        $this->assertEquals(PaymentStatus::FLAGGED, $payment->getStatus(), 'Payment should be FLAGGED');


        $this->deleteProfile($this->contractForCreate->getTenant()->getAciCollectPayProfileId());
    }
}
