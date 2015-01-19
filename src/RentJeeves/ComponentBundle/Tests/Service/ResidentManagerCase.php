<?php
namespace RentJeeves\ComponentBundle\Tests\Service;

use RentJeeves\ComponentBundle\Service\ResidentManager;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\TestBundle\BaseTestCase;
use Doctrine\ORM\EntityManager;

class ResidentManagerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldValidate()
    {
        $this->load(true);
        /** @var $resident ResidentManager */
        $resident = $this->getContainer()->get('resident_manager');
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');

        $residentMapping = new ResidentMapping();
        $residentMapping->setTenant($tenant);
        $residentMapping->setHolding($landlord->getHolding());

        $errors = $resident->validate($landlord, $residentMapping);
        $this->assertCount(1, $errors);
        $this->assertEquals('common.residentId.required', end($errors));
        $residentMapping->setResidentId('t0011984');
        $errors = $resident->validate($landlord, $residentMapping);
        $this->assertCount(1, $errors);
        $error = end($errors);
        $this->assertEquals('error.residentId.already_use', $error);
    }

    /**
     * @test
     * @depends shouldValidate
     */
    public function shouldClearWaitingRoom()
    {
        $this->load(true);
        /** @var $resident ResidentManager */
        $resident = $this->getContainer()->get('resident_manager');
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractsWaiting);

        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('john@rentrack.com');
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');

        $residentMapping = new ResidentMapping();
        $residentMapping->setTenant($tenant);
        $residentMapping->setHolding($landlord->getHolding());
        $residentMapping->setResidentId('t0013535');
        $resident->validate($landlord, $residentMapping);

        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(0, $contractsWaiting);
    }

    /**
     * @test
     */
    public function shouldNotHaveMultipleContracts()
    {
        $this->load(true);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        /** @var $resident ResidentManager */
        $resident = $this->getContainer()->get('resident_manager');
        $hasMultipleContracts = $resident->hasMultipleContracts($tenant, $landlord->getHolding());
        $this->assertFalse($hasMultipleContracts);
    }

    /**
     * We check: "Don't create double entity on the residentMapping entity", we check before make changes and count it,
     * get 2, on the end of test we again count and get 2 - it's means we don't create double entityMapping
     * 
     * @test
     */
    public function shouldUpdateExistResidentId()
    {
        $this->load(true);
        /** @var $resident ResidentManager */
        $resident = $this->getContainer()->get('resident_manager');
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $residentMapping = $em->getRepository('RjDataBundle:ResidentMapping')->findAll();
        $this->assertCount(2, $residentMapping);

        $residentMapping = new ResidentMapping();
        $residentMapping->setTenant($tenant);
        $residentMapping->setHolding($landlord->getHolding());

        $errors = $resident->validate($landlord, $residentMapping);
        $this->assertCount(1, $errors);
        $this->assertEquals('common.residentId.required', end($errors));
        $residentMapping->setResidentId('t0011985');
        $errors = $resident->validate($landlord, $residentMapping);
        $em->flush();
        $this->assertCount(0, $errors);
        $residentMapping = $em->getRepository('RjDataBundle:ResidentMapping')->findAll();
        $this->assertCount(2, $residentMapping);
    }
}
