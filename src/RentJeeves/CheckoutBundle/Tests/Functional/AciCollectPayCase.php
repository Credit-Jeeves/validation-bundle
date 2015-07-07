<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use ACI\Utils\OldProfilesStorage;
use Payum\AciCollectPay\Model\Profile;
use Payum\AciCollectPay\Request\ProfileRequest\DeleteProfile;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
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

        $contractToSelect = 2;
        $tenantEmail = 'tenant11@example.com';
        $this->contract = $this->getContract($tenantEmail, $contractToSelect);

        if ($profileId = $this->getOldProfileId(md5($this->contract->getTenant()->getId()))) {
            $this->deleteProfile($profileId);
        }

        $this->contract->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI_COLLECT_PAY);

        $this->contract->getGroup()->getDepositAccount()->setMerchantName(564075);

        $this->getEntityManager()->persist($this->contract->getGroup());

        $this->getEntityManager()->flush();

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
        $this->setDefaultSession('selenium2');
        $repo = $this->getEntityManager()->getRepository('RjDataBundle:PaymentAccount');

        $countsBefore = count($repo->findBy(['paymentProcessor' => PaymentProcessor::ACI_COLLECT_PAY]));
        $repo->clear();

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
                'rentjeeves_checkoutbundle_paymenttype_type' => PaymentTypeEnum::ONE_TIME,
                'rentjeeves_checkoutbundle_paymenttype_start_date' => date('n/j/Y'),
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

        $this->getEntityManager()->refresh($this->contract);
        $this->getEntityManager()->refresh($this->contract->getTenant());

        $this->assertNotEmpty($this->contract->getTenant()->getAciCollectPayProfileId());

        $this->setOldProfileId(
            md5($this->contract->getTenant()->getId()),
            $this->contract->getTenant()->getAciCollectPayProfileId()
        );

        $this->assertNotEmpty($this->contract->getAciCollectPayContractBilling());

        $merchantName = $this->contract->getGroup()->getDepositAccount()->getMerchantName();
        $this->assertEquals($merchantName, $this->contract->getAciCollectPayContractBilling()->getDivisionId());

        $countsAfter = count($repo->findBy(['paymentProcessor' => PaymentProcessor::ACI_COLLECT_PAY]));

        $this->assertEquals($countsBefore + 1, $countsAfter);

        $this->deleteProfile($this->contract->getTenant()->getAciCollectPayProfileId());
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
}
