<?php

namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class TenantSignUpCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldAllowTenantSignUpWithNotMatchedFirstLastNamesIfAllowPayAnythingEnabledAndGroupIntegrated()
    {
        $propertyId = 19;
        $unitId = 27;

        $this->load(true);
        $em = $this->getEntityManager();
        $unit = $em->find('RjDataBundle:Unit', $unitId);
        /** @var Unit $unit */
        $this->assertNotNull($unit, 'Unit #27 should exist');
        $this->assertTrue($unit->getProperty()->isSingle(), 'Property should be SINGLE');
        $this->assertEquals($propertyId, $unit->getProperty()->getId(), 'Unit #27 should belong to single property#19');
        $groupSettings = $unit->getGroup()->getGroupSettings();
        $groupSettings->setIsIntegrated(true);
        $em->flush($groupSettings);
        $this->assertFalse($groupSettings->isAllowPayAnything(), 'PayAnything should be disabled');

        /** @var Tenant $tenant */
        $tenant = $this->getContainer()->get('renttrack.user_creator')->createTenant('Mark', 'Totti');

        $contractWaiting = new Contract();
        $contractWaiting->setStatus(ContractStatus::WAITING);
        $contractWaiting->setUnit($unit);
        $contractWaiting->setProperty($unit->getProperty());
        $contractWaiting->setGroup($unit->getGroup());
        $contractWaiting->setHolding($unit->getHolding());

        $residentMapping = new ResidentMapping();
        $residentMapping->setHolding($contractWaiting->getHolding());
        $residentMapping->setTenant($tenant);
        $residentMapping->setResidentId('r548787');
        $tenant->addResidentsMapping($residentMapping);
        $contractWaiting->setTenant($tenant);

        $contractWaiting->setRent(111);
        $contractWaiting->setStartAt(new \DateTime());
        $contractWaiting->setFinishAt(new \DateTime());
        $em->persist($residentMapping);
        $em->persist($contractWaiting);
        $em->flush();

        $this->setDefaultSession('selenium2');
        $this->session->visit(
            sprintf('%suser/new/%s', $this->getUrl(), $propertyId)
        );
        $thisIsMyRentBtn = $this->page->find('css', 'button.thisIsMyRental');
        $this->assertNotNull($thisIsMyRentBtn, 'ThisIsMyRental button not found');
        $thisIsMyRentBtn->click();
        $newUserForm = $this->page->find('css', '#formNewUser');
        $this->fillForm(
            $newUserForm,
            [
                'rentjeeves_publicbundle_tenanttype_first_name' => 'Tomas', // set not matched name
                'rentjeeves_publicbundle_tenanttype_last_name'  => 'Totti',
                'rentjeeves_publicbundle_tenanttype_email' => 'tomas_totti@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => '123',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => '123',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
            ]
        );
        $this->page->pressButton('continue');
        $this->session->wait($this->timeout, '$(".error_list").length > 0');
        $errorList = $this->page->findAll('css', '.error_list');
        $this->assertCount(1, $errorList, 'One error expected');
        $this->assertEquals('error.unit.reserved', $errorList[0]->getText(), 'Error should be "error.unit.reserved"');

        $groupSettings->setAllowPayAnything(true);
        $em->flush($groupSettings);

        $thisIsMyRentBtn = $this->page->find('css', 'button.thisIsMyRental');
        $this->assertNotNull($thisIsMyRentBtn, 'ThisIsMyRental button not found');
        $thisIsMyRentBtn->click();
        $newUserForm = $this->page->find('css', '#formNewUser');
        $this->fillForm(
            $newUserForm,
            [
                'rentjeeves_publicbundle_tenanttype_first_name' => 'Frank', // set not matched name
                'rentjeeves_publicbundle_tenanttype_last_name'  => 'Gaudi',
                'rentjeeves_publicbundle_tenanttype_email' => 'frank_gaudi@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => '123',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => '123',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
            ]
        );
        $this->page->pressButton('continue');
        $this->session->wait($this->timeout, '$("h3.title:contains(\'verify.email\')").length > 0');
        $this->assertContains('/new/send/', $this->session->getCurrentUrl(), 'Location should be /new/send/');

        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractWaiting->getId());
        $this->assertNotNull($contract, 'Contract with unit #27 should be moved out of waiting!');
        $this->assertEquals(ContractStatus::WAITING, $contract->getStatus(), 'Contract should not change its state');

        $newTenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('frank_gaudi@mail.com');
        $this->assertNotNull($newTenant, 'New tenant should be created');
        $this->assertCount(1, $newTenant->getContracts(), 'New contract should be created b/c WAITING didn\'t match');
    }
}
