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
        /**
         * @var $resident ResidentManager
         */
        $resident = $this->getContainer()->get('resident_manager');
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );

        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneBy(
            array(
                'email' => 'landlord1@example.com'
            )
        );

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
        /**
         * @var $resident ResidentManager
         */
        $resident = $this->getContainer()->get('resident_manager');
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractsWaiting);

        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );

        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneBy(
            array(
                'email' => 'landlord1@example.com'
            )
        );

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
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );

        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneBy(
            array(
                'email' => 'landlord1@example.com'
            )
        );
        /**
         * @var $resident ResidentManager
         */
        $resident = $this->getContainer()->get('resident_manager');
        $hasMultipleContracts = $resident->hasMultipleContracts($tenant, $landlord->getHolding());
        $this->assertFalse($hasMultipleContracts);
    }
}
