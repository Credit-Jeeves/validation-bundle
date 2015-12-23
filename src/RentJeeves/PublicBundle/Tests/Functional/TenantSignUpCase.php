<?php

namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
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

        $contractWaiting = new ContractWaiting();
        $contractWaiting->setUnit($unit);
        $contractWaiting->setProperty($unit->getProperty());
        $contractWaiting->setGroup($unit->getGroup());
        $contractWaiting->setFirstName('Mark');
        $contractWaiting->setLastName('Totti');
        $contractWaiting->setRent(111);
        $contractWaiting->setResidentId('r548787');
        $contractWaiting->setStartAt(new \DateTime());
        $contractWaiting->setFinishAt(new \DateTime());
        $em->persist($contractWaiting);
        $em->flush($contractWaiting);
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneByUnit($unit);
        $this->assertNull($contract, 'Contract with unit #27 should not exist');

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
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneByUnit($unit);
        $this->assertNotNull($contract, 'Contract with unit #27 should be created from waiting!');
        $this->assertEquals(ContractStatus::PENDING, $contract->getStatus(), 'Contract should be created as PENDING');
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findOneByUnit($unit);
        $this->assertNotNull($contractWaiting, 'ContractWaiting with unit #27 should remain in the DB');
    }
}
