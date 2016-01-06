<?php

namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class CreateNewIntegrationTenantCase extends BaseTestCase
{
    protected $requestParameters = [
        'resid' => 'Test_Resident_111',
        'leaseid' => 'Test_Lease_111',
        'propid' => 'rnttrk02',
        'unitid' => 'Test_Unit_111',
        'rent' => 505,
        'appfee' => 101,
        'secdep' => 102,
        'redirect' => 'http://localhost',
    ];

    protected function prepareFixtures()
    {
        $em = $this->getEntityManager();

        $group = $em->find('DataBundle:Group', 25);
        $this->assertNotNull($group, 'Check fixtures, should exist group with id 25');
        $property = $em->find('RjDataBundle:Property', 2);
        $this->assertNotNull($property, 'Check fixtures, should exist property with id 2');
        $property->removePropertyGroup($group);
        $group->removeGroupProperty($property);
        /** @var Unit $unit */
        $unit = $property->getUnits()->last();
        $this->assertNotNull($unit, 'Check fixtures, property with id 2 should have at least one unit');
        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unitMapping->setExternalUnitId($this->requestParameters['unitid']);
        $unit->setUnitMapping($unitMapping);
        $em->persist($unitMapping);
        $em->persist($property);
        $em->persist($group);
        $group2 = $em->find('DataBundle:Group', 24);
        $this->assertNotNull($group2, 'Check fixtures, should exist group with id 24');
        $group2->getGroupSettings()->setAllowPayAnything(true);
        $depositAccount = clone $group2->getDepositAccountForCurrentPaymentProcessor(
            DepositAccountType::APPLICATION_FEE
        );
        $depositAccount->setType(DepositAccountType::SECURITY_DEPOSIT);
        $em->persist($depositAccount);

        $em->flush();
        $em->clear();
    }

    /**
     * @test
     */
    public function shouldCreateUserByFullParameters()
    {
        $this->load(true);
        $this->prepareFixtures();
        $parameters = $this->requestParameters;
        $this->setDefaultSession('selenium2');

        $this->session->visit($this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters));
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $redirectedUrl = $this->getUrl() . 'user/new/2/property';
        $this->assertEquals(
            $redirectedUrl,
            $this->session->getCurrentUrl(),
            'Should redirection to ' . $redirectedUrl
        );
        $selectedUnit = $this->getDomElement(
            '#idUnit2 option:contains("27-f")',
            'Unit select should be present on the page.'
        );
        $this->assertTrue((bool) $selectedUnit->getAttribute('selected'), 'Unassigned unit should be selected');
        $btn = $this->getDomElement('button.thisIsMyRental', '"This is my rental" button does not exist.');
        $btn->click();
        $form = $this->getDomElement('#formNewUser', 'Form for create new user should be present.');
        $this->fillForm(
            $form,
            [
                'rentjeeves_publicbundle_tenanttype_first_name' => 'FirstN',
                'rentjeeves_publicbundle_tenanttype_last_name' => 'LastN',
                'rentjeeves_publicbundle_tenanttype_email' => 'externaluser1@example.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => 1
            ]
        );

        $regBtn = $this->getDomElement('#register', 'Register button should be present');
        $regBtn->click();
        $this->session->wait($this->timeout, '$(\'h3.title:contains("verify.email")\').length');
        /** @var Tenant $tenant */
        $tenant = $this->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('externaluser1@example.com');
        $this->assertNotNull($tenant, 'Tenant was not created');
        $this->assertFalse($tenant->getResidentsMapping()->isEmpty(), 'Resident mapping was not created');
        $this->assertCount(1, $tenant->getResidentsMapping(), 'Should be created just one resident mapping');
        /** @var ResidentMapping $residentMapping */
        $residentMapping = $tenant->getResidentsMapping()->first();
        $this->assertEquals($parameters['resid'], $residentMapping->getResidentId(), 'Resident id is invalid.');
        $this->assertCount(1, $tenant->getContracts(), 'Should be created 1 contract');
        /** @var Contract $contract */
        $contract = $tenant->getContracts()->first();
        $this->assertEquals($parameters['leaseid'], $contract->getExternalLeaseId(), 'Lease id is invalid.');
        $this->assertEquals(
            '27-f',
            $contract->getUnit()->getName(),
            'Contract should have unit id with name "27-f" or last unit belong property #2'
        );
        $this->assertEquals($parameters['rent'], $contract->getRent(), 'Rent is invalid');

        // login
        $this->login('externaluser1@example.com', 'pass');
        $this->getDomElement('#pay-anything-popup', 'Should be displayed pay anything popup');
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $payFor = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype_payFor');
        $this->assertEquals(
            DepositAccountType::APPLICATION_FEE,
            $payFor->getValue(),
            'Should be selected "application fee" on pay for field'
        );
        $amount = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype_amount');
        $this->assertEquals(
            $parameters['appfee'],
            $amount->getValue(),
            'Should be prefilled amount to ' . $parameters['appfee']
        );
        $payFor->setValue(DepositAccountType::SECURITY_DEPOSIT);
        $this->assertEquals(
            $parameters['secdep'],
            $amount->getValue(),
            'Should be prefilled amount to ' . $parameters['secdep']
        );
    }

    /**
     * @test
     * @depends shouldCreateUserByFullParameters
     */
    public function shouldRedirectIfHasRedirectAndAmountsParams()
    {
        $startDateField = $this->getDomElement(
            '#rentjeeves_checkoutbundle_payanything_paymenttype_start_date',
            'Start date field not found'
        );
        $startDateField->setValue((new \DateTime('tomorrow'))->format('m/d/Y'));

        $nextBtn = $this->getDomElement('#pay-anything-popup button span:contains("pay_popup.step.next")');
        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $payAccountId = 'rentjeeves_checkoutbundle_paymentaccounttype_pay_anything';
        $form = $this->page->find('css', '#' . $payAccountId);

        $this->fillForm(
            $form,
            [
                $payAccountId . '_name' => 'Test Bank Acc',
                $payAccountId . '_PayorName' => 'FirstN LastN',
                $payAccountId . '_RoutingNumber' => '062202574',
                $payAccountId . '_AccountNumber_AccountNumber' => '123245678',
                $payAccountId . '_AccountNumber_AccountNumberAgain' => '123245678',
                $payAccountId . '_ACHDepositType_0' => true,
            ]
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $makeBtn = $this->getDomElement('#pay-anything-popup button span:contains("checkout.make_payment")');
        $makeBtn->click();

        $this->session->wait(
            $this->timeout,
            '$("button span:contains(\'pay_popup.close\')").is(":visible")'
        );

        $closeBtn = $this->getDomElement('#pay-anything-popup button span:contains("pay_popup.close")');
        $closeBtn->click();

        $this->session->wait($this->timeout, '(document.readyState == "complete")'); // wait reload page

        $this->getDomElement('#pay-anything-popup', 'Should be displayed pay anything popup again');
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");

        $infoMessage = $this->getDomElement(
            '#pay-anything-popup .information-box.pie-el',
            'Should be displayed information message'
        );
        $this->assertEquals(
            'pay_anything_popup.should_pay_message',
            $infoMessage->getText(),
            'Should be displayed info message that need payed also application fee'
        );
        $payFor = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype_payFor');
        $this->assertEquals(
            DepositAccountType::APPLICATION_FEE,
            $payFor->getValue(),
            'Should be selected "application fee" on pay for field'
        );
        $amount = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype_amount');
        $this->assertEquals(
            $this->requestParameters['appfee'],
            $amount->getValue(),
            'Should be prefilled amount to ' . $this->requestParameters['appfee']
        );
        $payFor->setValue(DepositAccountType::SECURITY_DEPOSIT);
        $this->assertEquals(
            '',
            $amount->getValue(),
            'Should be clean prefilled amount for already payed security deposit.'
        );
        $payFor->setValue(DepositAccountType::APPLICATION_FEE);

        $startDateField->setValue((new \DateTime('tomorrow'))->format('m/d/Y'));
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");

        $nextBtn->click();
        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $paymentAcc = $this->getDomElement('#pay-anything-popup span:contains("Test Bank Acc")');
        $paymentAcc->click();

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $makeBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $closeBtn->click();

        $this->assertStringStartsWith(
            $this->requestParameters['redirect'],
            $this->session->getCurrentUrl(),
            'Should redirect to ' . $this->requestParameters['redirect']
        );
    }

    /**
     * @test
     */
    public function shouldCreateUserByPartialParameters()
    {
        $this->load(true);
        $this->prepareFixtures();
        $parameters = $this->requestParameters;
        $this->setDefaultSession('selenium2');

        unset($parameters['unitid']);
        unset($parameters['rent']);
        unset($parameters['appfee']);
        $this->session->visit($this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters));
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $redirectedUrl = $this->getUrl() . 'user/new/2/property';
        $this->assertEquals(
            $redirectedUrl,
            $this->session->getCurrentUrl(),
            'Should redirection to ' . $redirectedUrl
        );
        $selectedUnit = $this->getDomElement(
            '#idUnit2 option.unassignedUnit',
            'Unit select should be present on the page.'
        );
        $this->assertTrue((bool) $selectedUnit->getAttribute('selected'), 'Unassigned unit should be selected');
        $btn = $this->getDomElement('button.thisIsMyRental', '"This is my rental" button does not exist.');
        $btn->click();
        $form = $this->getDomElement('#formNewUser', 'Form for create new user should be present.');
        $this->fillForm(
            $form,
            [
                'rentjeeves_publicbundle_tenanttype_first_name' => 'FirstN',
                'rentjeeves_publicbundle_tenanttype_last_name' => 'LastN',
                'rentjeeves_publicbundle_tenanttype_email' => 'externaluser1@example.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => 1,
            ]
        );

        $regBtn = $this->getDomElement('#register', 'Register button should be present');
        $regBtn->click();
        $this->session->wait($this->timeout, '$(\'h3.title:contains("verify.email")\').length');
        /** @var Tenant $tenant */
        $tenant = $this->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('externaluser1@example.com');
        $this->assertNotNull($tenant, 'Tenant was not created');
        $this->assertFalse($tenant->getResidentsMapping()->isEmpty(), 'Resident mapping was not created');
        $this->assertCount(1, $tenant->getResidentsMapping(), 'Should be created just one resident mapping');
        /** @var ResidentMapping $residentMapping */
        $residentMapping = $tenant->getResidentsMapping()->first();
        $this->assertEquals($parameters['resid'], $residentMapping->getResidentId(), 'Resident id is invalid.');
        $this->assertCount(1, $tenant->getContracts(), 'Should be created 1 contract');
        /** @var Contract $contract */
        $contract = $tenant->getContracts()->first();
        $this->assertEquals($parameters['leaseid'], $contract->getExternalLeaseId(), 'Lease id is invalid.');
        $this->assertEquals(
            Unit::SEARCH_UNIT_UNASSIGNED,
            $contract->getSearch(),
            'Contract "search" field should be set to "unassigned"'
        );

        // login
        $this->login('externaluser1@example.com', 'pass');
        $this->getDomElement('#pay-anything-popup', 'Should be displayed pay anything popup');
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $payFor = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype_payFor');
        $this->assertEquals(
            DepositAccountType::SECURITY_DEPOSIT,
            $payFor->getValue(),
            'Should be selected "application fee" on pay for field'
        );
        $amount = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype_amount');
        $this->assertEquals(
            $parameters['secdep'],
            $amount->getValue(),
            'Should be prefilled amount to ' . $parameters['secdep']
        );
        $payFor->setValue(DepositAccountType::APPLICATION_FEE);
        $this->assertEquals(
            '',
            $amount->getValue(),
            'Should be clean prefilled amount'
        );
    }

    /**
     * @test
     */
    public function shouldNotRedirectIfEmptyRedirectParam()
    {
        unset($this->requestParameters['redirect']);

        $this->shouldCreateUserByFullParameters();

        $startDateField = $this->getDomElement(
            '#rentjeeves_checkoutbundle_payanything_paymenttype_start_date',
            'Start date field not found'
        );
        $startDateField->setValue((new \DateTime('tomorrow'))->format('m/d/Y'));

        $nextBtn = $this->getDomElement('#pay-anything-popup button span:contains("pay_popup.step.next")');
        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $payAccountId = 'rentjeeves_checkoutbundle_paymentaccounttype_pay_anything';
        $form = $this->getDomElement('#' . $payAccountId);

        $this->fillForm(
            $form,
            [
                $payAccountId . '_name' => 'Test Bank Acc',
                $payAccountId . '_PayorName' => 'FirstN LastN',
                $payAccountId . '_RoutingNumber' => '062202574',
                $payAccountId . '_AccountNumber_AccountNumber' => '123245678',
                $payAccountId . '_AccountNumber_AccountNumberAgain' => '123245678',
                $payAccountId . '_ACHDepositType_0' => true,
            ]
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $makeBtn = $this->getDomElement('#pay-anything-popup button span:contains("checkout.make_payment")');
        $makeBtn->click();

        $this->session->wait(
            $this->timeout,
            '$("button span:contains(\'pay_popup.close\')").is(":visible")'
        );

        $closeBtn = $this->getDomElement('#pay-anything-popup button span:contains("pay_popup.close")');
        $closeBtn->click();

        $this->session->wait($this->timeout, '(document.readyState == "complete")'); // wait reload page

        $this->getDomElement('#pay-anything-popup', 'Should be displayed pay anything popup again');
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");

        $infoMessage = $this->getDomElement(
            '#pay-anything-popup .information-box.pie-el',
            'Should be displayed information message'
        );
        $this->assertEquals(
            'pay_anything_popup.should_pay_message',
            $infoMessage->getText(),
            'Should be displayed info message that need payed also application fee'
        );
        $payFor = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype_payFor');
        $this->assertEquals(
            DepositAccountType::APPLICATION_FEE,
            $payFor->getValue(),
            'Should be selected "application fee" on pay for field'
        );
        $amount = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype_amount');
        $this->assertEquals(
            $this->requestParameters['appfee'],
            $amount->getValue(),
            'Should be prefilled amount to ' . $this->requestParameters['appfee']
        );
        $payFor->setValue(DepositAccountType::SECURITY_DEPOSIT);
        $this->assertEquals(
            '',
            $amount->getValue(),
            'Should be clean prefilled amount for already payed security deposit.'
        );
        $payFor->setValue(DepositAccountType::APPLICATION_FEE);

        $startDateField->setValue((new \DateTime('tomorrow'))->format('m/d/Y'));
        $this->session->wait($this->timeout, "!$('#ui-datepicker-div').is(':visible')");

        $nextBtn->click();
        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $paymentAcc = $this->getDomElement('#pay-anything-popup span:contains("Test Bank Acc")');
        $paymentAcc->click();

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $makeBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $closeBtn->click();

        $this->assertStringStartsWith(
            $this->getUrl(),
            $this->session->getCurrentUrl(),
            'Should not be redirected'
        );
    }

    /**
     * @test
     */
    public function shouldNotRedirectIfEmptyAmountsParams()
    {
        $this->load(true);
        $this->prepareFixtures();
        $parameters = $this->requestParameters;
        $this->setDefaultSession('selenium2');

        unset($parameters['unitid']);
        unset($parameters['rent']);
        unset($parameters['appfee']);
        unset($parameters['secdep']);
        unset($parameters['redirect']);
        $this->session->visit($this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters));
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $redirectedUrl = $this->getUrl() . 'user/new/2/property';
        $this->assertEquals(
            $redirectedUrl,
            $this->session->getCurrentUrl(),
            'Should redirection to ' . $redirectedUrl
        );
        $selectedUnit = $this->getDomElement(
            '#idUnit2 option.unassignedUnit',
            'Unit select should be present on the page.'
        );
        $this->assertTrue((bool) $selectedUnit->getAttribute('selected'), 'Unassigned unit should be selected');
        $btn = $this->getDomElement('button.thisIsMyRental', '"This is my rental" button does not exist.');
        $btn->click();
        $form = $this->getDomElement('#formNewUser', 'Form for create new user should be present.');
        $this->fillForm(
            $form,
            [
                'rentjeeves_publicbundle_tenanttype_first_name' => 'FirstN',
                'rentjeeves_publicbundle_tenanttype_last_name' => 'LastN',
                'rentjeeves_publicbundle_tenanttype_email' => 'externaluser1@example.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => 1,
            ]
        );

        $regBtn = $this->getDomElement('#register', 'Register button should be present');
        $regBtn->click();
        $this->session->wait($this->timeout, '$(\'h3.title:contains("verify.email")\').length');
        /** @var Tenant $tenant */
        $tenant = $this->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('externaluser1@example.com');
        $this->assertNotNull($tenant, 'Tenant was not created');
        // login
        $this->login('externaluser1@example.com', 'pass');

        $payAnythingDialog = $this->getDomElement('#pay-anything-popup', 'Should be displayed pay anything popup');

        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");

        $paymentForm = $this->getDomElement('#rentjeeves_checkoutbundle_payanything_paymenttype');

        $startDate = (new \DateTime('tomorrow'))->format('m/d/Y');

        $this->fillForm(
            $paymentForm,
            [
                'rentjeeves_checkoutbundle_payanything_paymenttype_payFor' => DepositAccountType::APPLICATION_FEE,
                'rentjeeves_checkoutbundle_payanything_paymenttype_amount' => 112,
                'rentjeeves_checkoutbundle_payanything_paymenttype_start_date' => $startDate,
            ]
        );

        $nextBtn = $this->getDomElement('#pay-anything-popup button span:contains("pay_popup.step.next")');
        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $payAccountId = 'rentjeeves_checkoutbundle_paymentaccounttype_pay_anything';
        $form = $this->getDomElement('#' . $payAccountId);

        $this->fillForm(
            $form,
            [
                $payAccountId . '_name' => 'Test Bank Acc',
                $payAccountId . '_PayorName' => 'FirstN LastN',
                $payAccountId . '_RoutingNumber' => '062202574',
                $payAccountId . '_AccountNumber_AccountNumber' => '123245678',
                $payAccountId . '_AccountNumber_AccountNumberAgain' => '123245678',
                $payAccountId . '_ACHDepositType_0' => true,
            ]
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $makeBtn = $this->getDomElement('#pay-anything-popup button span:contains("checkout.make_payment")');
        $makeBtn->click();

        $this->session->wait(
            $this->timeout,
            '$("button span:contains(\'pay_popup.close\')").is(":visible")'
        );

        $closeBtn = $this->getDomElement('#pay-anything-popup button span:contains("pay_popup.close")');
        $closeBtn->click();

        $this->session->wait($this->timeout, '(document.readyState == "complete")'); // wait reload page

        $this->assertStringStartsWith(
            $this->getUrl(),
            $this->session->getCurrentUrl(),
            'Should not be redirected'
        );

        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");

        $this->assertFalse(
            $payAnythingDialog->isVisible(),
            'Should not be displayed pay anything popup automatically'
        );

        $this->session->executeScript('$("#pay-anything-1").click();');
        // use this hack b/c selenium not work link under other elements

        $this->assertTrue(
            $payAnythingDialog->isVisible(),
            'Should be displayed pay anything popup'
        );

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $infoMessage = $this->getDomElement('#pay-anything-popup .information-box.pie-el');
        $this->assertFalse(
            $infoMessage->isVisible(),
            'Should not be displayed information message'
        );

        $this->fillForm(
            $paymentForm,
            [
                'rentjeeves_checkoutbundle_payanything_paymenttype_payFor' => DepositAccountType::SECURITY_DEPOSIT,
                'rentjeeves_checkoutbundle_payanything_paymenttype_amount' => 121,
                'rentjeeves_checkoutbundle_payanything_paymenttype_start_date' => $startDate,
            ]
        );

        $nextBtn->click();
        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $paymentAcc = $this->getDomElement('#pay-anything-popup span:contains("Test Bank Acc")');
        $paymentAcc->click();

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $makeBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $closeBtn->click();

        $this->assertStringStartsWith(
            $this->getUrl(),
            $this->session->getCurrentUrl(),
            'Should not be redirected'
        );

        $this->assertFalse(
            $payAnythingDialog->isVisible(),
            'Should not be displayed pay anything popup automatically'
        );
    }

    /**
     * @return array
     */
    public function shouldCheckRequiredParametersDataProvider()
    {
        return [
            [$this->requestParameters, 'resid'],
            [$this->requestParameters, 'leaseid'],
            [$this->requestParameters, 'propid'],
        ];
    }

    /**
     * @param array $parameters
     * @param string $parameterName
     *
     * @test
     * @dataProvider shouldCheckRequiredParametersDataProvider
     * @depends shouldCreateUserByFullParameters
     */
    public function shouldCheckRequiredParameters(array $parameters, $parameterName)
    {
        $this->setDefaultSession('goutte');

        unset($parameters[$parameterName]);
        $this->session->visit($this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters));

        $this->assertEquals(
            400,
            $this->session->getStatusCode(),
            sprintf(
                'Invalid status code should be 400 expected %d',
                $this->session->getStatusCode()
            )
        );
    }

    /**
     * @return array
     */
    public function shouldCheckNotRequiredParametersDataProvider()
    {
        return [
            [$this->requestParameters, 'unitid'],
            [$this->requestParameters, 'rent'],
            [$this->requestParameters, 'appfee'],
            [$this->requestParameters, 'secdep'],
            [$this->requestParameters, 'redirect'],
        ];
    }

    /**
     * @param array $parameters
     * @param string $parameterName
     *
     * @test
     * @dataProvider shouldCheckNotRequiredParametersDataProvider
     * @depends shouldCreateUserByFullParameters
     */
    public function shouldCheckNotRequiredParameters(array $parameters, $parameterName)
    {
        $this->setDefaultSession('goutte');

        unset($parameters[$parameterName]);
        $this->session->visit($this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters));

        $this->assertEquals(
            200,
            $this->session->getStatusCode(),
            sprintf(
                'Invalid status code should be 400 expected %d',
                $this->session->getStatusCode()
            )
        );

        $redirectedUrl = $this->getUrl() . 'user/new/2/property';
        $this->assertEquals(
            $redirectedUrl,
            $this->session->getCurrentUrl(),
            'Should redirection to ' . $redirectedUrl
        );
    }

    /**
     * @test
     */
    public function shouldCheckPropertyBelongOneGroup()
    {
        $this->load(true);
        $this->setDefaultSession('goutte');
        $property = $this->getEntityManager()->find('RjDataBundle:Property', 2);
        $this->assertNotNull($property, 'Check fixtures, should exist property with id 2');
        $this->assertCount(2, $property->getPropertyGroups(), 'Check fixtures, property #2 should belong to 2 groups');
        $parameters = $this->requestParameters;
        unset($parameters['unitid']); // should remove unitid b/c we do not have unit mapping
        $this->session->visit(
            $this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters)
        );
        $this->assertEquals(
            412,
            $this->session->getStatusCode(),
            'Invalid status code should be 412 expected ' . $this->session->getStatusCode()
        );
    }

    /**
     * @test
     * @depends shouldCheckPropertyBelongOneGroup
     */
    public function shouldCheckPropertyHasUnitsBelongOneGroup()
    {
        $this->setDefaultSession('goutte');
        $this->prepareFixtures();

        $em = $this->getEntityManager();
        $property = $em->find('RjDataBundle:Property', 2);
        $this->assertCount(1, $property->getPropertyGroups(), 'Check fixtures, property #2 should belong to one group');
        $this->assertGreaterThanOrEqual(
            2,
            $property->getUnits()->count(),
            'Check fixtures, property #2 should have at least 2 units'
        );
        $group1 = $em->find('DataBundle:Group', 24);
        $this->assertNotNull($group1, 'Check fixtures, should exist group with id 24');
        $group2 = $em->find('DataBundle:Group', 25);
        $this->assertNotNull($group2, 'Check fixtures, should exist group with id 25');
        /** @var Unit $unit1 */
        $unit1 = $property->getUnits()->first();
        $unit1->setGroup($group1);
        /** @var Unit $unit2 */
        $unit2 = $property->getUnits()->last();
        $unit2->setGroup($group2);
        $em->persist($unit1);
        $em->persist($unit2);
        $em->flush();

        $this->session->visit(
            $this->getUrl() . 'user/integration/new/yardi?' . http_build_query($this->requestParameters)
        );
        $this->assertEquals(
            412,
            $this->session->getStatusCode(),
            'Invalid status code should be 412 expected ' . $this->session->getStatusCode()
        );
    }
}
