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
    static $requestParameters = [
        'resid' => 'Test_Resident_111',
        'leasid' => 'Test_Lease_111',
        'propid' => 'rnttrk02',
        'unitid' => 'Test_Unit_111',
        'rent' => 505,
        'appfee' => 101,
        'secdep' => 102,
    ];

    protected function preparedFixtures()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 25);
        $this->assertNotNull($group, 'Check fixtures, should exist group with id 25');
        $property = $this->getEntityManager()->find('RjDataBundle:Property', 2);
        $this->assertNotNull($property, 'Check fixtures, should exist property with id 2');
        $property->removePropertyGroup($group);
        $group->removeGroupProperty($property);
        /** @var Unit $unit */
        $unit = $property->getUnits()->last();
        $this->assertNotNull($unit, 'Check fixtures, property with id 2 should have at least one unit');
        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unitMapping->setExternalUnitId(self::$requestParameters['unitid']);
        $unit->setUnitMapping($unitMapping);
        $this->getEntityManager()->persist($unitMapping);
        $this->getEntityManager()->persist($property);
        $this->getEntityManager()->persist($group);
        $group2 = $this->getEntityManager()->find('DataBundle:Group', 24);
        $this->assertNotNull($group2, 'Check fixtures, should exist group with id 24');
        $group2->getGroupSettings()->setAllowPayAnything(true);
        $depositAccount = clone $group2->getDepositAccountForCurrentPaymentProcessor(
            DepositAccountType::APPLICATION_FEE
        );
        $depositAccount->setType(DepositAccountType::SECURITY_DEPOSIT);
        $this->getEntityManager()->persist($depositAccount);

        $this->getEntityManager()->flush();
    }

    /**
     * @test
     */
    public function shouldCreateUserByFullParameters()
    {
        $this->load(true);
        $this->preparedFixtures();
        $parameters = self::$requestParameters;
        $this->setDefaultSession('selenium2');

        $this->session->visit($this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters));
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $redirectedUrl = $this->getUrl() . 'user/new/2/property';
        $this->assertTrue(
            $this->session->getCurrentUrl() === $redirectedUrl,
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
            ]
        );
        $tos = $this->getDomElement('#rentjeeves_publicbundle_tenanttype_tos');
        $tos->click();

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
        $this->assertEquals($parameters['leasid'], $contract->getExternalLeaseId(), 'Lease id is invalid.');
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
     */
    public function shouldCreateUserByPartlyParameters()
    {
        $this->load(true);
        $this->preparedFixtures();
        $parameters = self::$requestParameters;
        $this->setDefaultSession('selenium2');

        unset($parameters['unitid']);
        unset($parameters['rent']);
        unset($parameters['appfee']);
        $this->session->visit($this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters));
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $redirectedUrl = $this->getUrl() . 'user/new/2/property';
        $this->assertTrue(
            $this->session->getCurrentUrl() === $redirectedUrl,
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
            ]
        );
        $tos = $this->getDomElement('#rentjeeves_publicbundle_tenanttype_tos');
        $tos->click();

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
        $this->assertEquals($parameters['leasid'], $contract->getExternalLeaseId(), 'Lease id is invalid.');
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
     * @return array
     */
    public function shouldCheckRequiredParametersDataProvider()
    {
        return [
            [self::$requestParameters, 'resid', 400, false],
            [self::$requestParameters, 'leasid', 400, false],
            [self::$requestParameters, 'propid', 400, false],
            [self::$requestParameters, 'unitid', 200, true], // unitid is not required
            [self::$requestParameters, 'rent', 200, true], // rent is not required
            [self::$requestParameters, 'appfee', 200, true], // appfee is not required
            [self::$requestParameters, 'secdep', 200, true], // secdep is not required
        ];
    }

    /**
     * @param array $parameters
     * @param string $requiredParameterName
     * @param int $resultStatusCode
     * @param bool $shouldRedirect
     *
     * @test
     * @dataProvider shouldCheckRequiredParametersDataProvider
     */
    public function shouldCheckRequiredParameters(
        array $parameters,
        $requiredParameterName,
        $resultStatusCode,
        $shouldRedirect
    ) {
        $this->load(true);
        $this->preparedFixtures();
        $this->setDefaultSession('goutte');

        unset($parameters[$requiredParameterName]);
        $this->session->visit($this->getUrl() . 'user/integration/new/yardi?' . http_build_query($parameters));

        $redirectedUrl = $this->getUrl() . 'user/new/2/property';

        $this->assertEquals(
            $resultStatusCode,
            $this->session->getStatusCode(),
            sprintf(
                'Invalid status code should be %d expected %d',
                $resultStatusCode,
                $this->session->getStatusCode()
            )
        );
        $this->assertTrue(
            ($shouldRedirect && $this->session->getCurrentUrl() === $redirectedUrl) ||
            (!$shouldRedirect && $this->session->getCurrentUrl() !== $redirectedUrl),
            sprintf(
                'Should%s redirection to %s.',
                $shouldRedirect ? '' : ' not',
                $redirectedUrl
            )
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
        $parameters = self::$requestParameters;
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
     */
    public function shouldCheckPropertyHasUnitsBelongOneGroup()
    {
        $this->load(true);
        $this->preparedFixtures();
        $this->setDefaultSession('goutte');
        $property = $this->getEntityManager()->find('RjDataBundle:Property', 2);
        $this->assertCount(1, $property->getPropertyGroups(), 'Check fixtures, property #2 should belong to one group');
        $this->assertGreaterThanOrEqual(
            2,
            $property->getUnits()->count(),
            'Check fixtures, property #2 should have at least 2 units'
        );
        $group1 =  $this->getEntityManager()->find('DataBundle:Group', 24);
        $this->assertNotNull($group1, 'Check fixtures, should exist group with id 24');
        $group2 =  $this->getEntityManager()->find('DataBundle:Group', 25);
        $this->assertNotNull($group2, 'Check fixtures, should exist group with id 25');
        /** @var Unit $unit1 */
        $unit1 = $property->getUnits()->first();
        $unit1->setGroup($group1);
        /** @var Unit $unit2 */
        $unit2 = $property->getUnits()->last();
        $unit2->setGroup($group2);
        $this->getEntityManager()->persist($unit1);
        $this->getEntityManager()->persist($unit2);
        $this->getEntityManager()->flush();

        $this->session->visit(
            $this->getUrl() . 'user/integration/new/yardi?' . http_build_query(self::$requestParameters)
        );
        $this->assertEquals(
            412,
            $this->session->getStatusCode(),
            'Invalid status code should be 412 expected ' . $this->session->getStatusCode()
        );
    }
}
