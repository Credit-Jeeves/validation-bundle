<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use ACI\Utils\OldProfilesStorage;
use Doctrine\ORM\EntityManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\ACI\AciCollectPay\EnrollmentManager;
use RentJeeves\DataBundle\Entity\AciCollectPaySettings;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use DateTime;
use Symfony\Component\Config\FileLocator;

class AciCollectPayCase extends BaseTestCase
{
    use OldProfilesStorage;

    /**
     * @var string
     */
    protected $paidForString;
    /**
     * @var string
     */
    protected $payButtonName;
    /**
     * @var FileLocator
     */
    protected $fixtureLocator;
    /**
     * @var Contract
     */
    protected $contract;

    public function setUp()
    {
        parent::setUp();

        $this->load(true);

        $this->fixtureLocator = new FileLocator(
            [__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures']
        );

        if ($profileId = $this->getOldProfileId('tenant11examplecom')) {
            /** @var EnrollmentManager $enrollmentManager */
            $enrollmentManager = $this->getContainer()->get('payment.aci_collect_pay.enrollment_manager');

            $enrollmentManager->deleteProfile($profileId);

            $this->unsetOldProfileId($profileId);
        }

        $contractToSelect = 2;
        $tenantEmail = 'tenant11@example.com';
        $this->contract = $this->getContract($tenantEmail, $contractToSelect);

        $this->contract->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI_COLLECT_PAY);

        $paySettings = new AciCollectPaySettings();

        $paySettings->setBusinessId(564075);
        $paySettings->setHolderName('Test Holder');
        $paySettings->setGroup($this->contract->getGroup());

        $this->contract->getGroup()->setAciCollectPaySettings($paySettings);

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($this->contract->getGroup());
        $em->persist($paySettings);

        $em->flush();

        $this->paidForString = $this->getPaidForDate($this->contract)->format('Y-m-d');
        $this->payButtonName = "contract-pay-" . ($contractToSelect);
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
        $countsBefore = $this->contract->getTenant()->getPaymentAccounts()->filter(function ($paymentAccount) {
            if (PaymentProcessor::ACI_COLLECT_PAY == $paymentAccount->getPaymentProcessor()) {
                return true;
            }
            return false;
        })->count();

        $this->setDefaultSession('selenium2');

        $this->login('tenant11@example.com', 'pass');

        $this->page->pressButton($this->payButtonName);

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
                'rentjeeves_checkoutbundle_paymenttype_paidFor' => $this->paidForString,
                'rentjeeves_checkoutbundle_paymenttype_amount' => '1000',
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

        $this->page->clickLink('payment.account.new');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');

        $this->fillForm($form, $this->getPaymentAccountFormData($type));


        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait(
            $this->timeout + 85000, // local need more time for passed test
            "!jQuery('#id-source-step').is(':visible')"
        );

        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $em->refresh($this->contract);
        $em->refresh($this->contract->getTenant());

        $this->assertNotEmpty($this->contract->getTenant()->getAciCollectPayProfile());

        $this->setOldProfileId(
            'tenant11examplecom',
            $this->contract->getTenant()->getAciCollectPayProfile()->getProfileId()
        );

        $this->assertNotEmpty($this->contract->getAciCollectPayContractBilling());

        $countsAfter = $this->contract->getTenant()->getPaymentAccounts()->filter(function ($paymentAccount) {
            if (PaymentProcessor::ACI_COLLECT_PAY == $paymentAccount->getPaymentProcessor()) {
                return true;
            }
            return false;
        })->count();

        $this->assertEquals($countsBefore +1, $countsAfter);
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
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(array('email' => $tenantEmail));
        $contracts = array_reverse($tenant->getContracts()->toArray()); # contract buttons numbered from bottom up
        return $contracts[$contractIndex - 1];
    }

    private function getPaidForDate(Contract $contract)
    {
        $paidFor = new DateTime();
        $paidFor = $paidFor->setDate($paidFor->format('Y'), $paidFor->format('m'), $contract->getDueDate());

        return $paidFor;
    }
}
