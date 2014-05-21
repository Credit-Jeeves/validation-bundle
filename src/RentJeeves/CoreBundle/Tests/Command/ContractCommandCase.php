<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Command\ContractBalanceCommand;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\CoreBundle\DateTime;

class ContractCommandCase extends BaseTestCase
{
    public function dataForUpdateBalance()
    {
        return array(
            array($isIntegrated = true),
            array($isIntegrated = false),
        );
    }

    /**
      * @dataProvider dataForUpdateBalance
      * @test
     */
    public function updateBalance($isIntegrated)
    {
        $this->load(true);
        $today = new DateTime();
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contract = new Contract();
        $contract->setRent(999999.00);
        $contract->setBalance(0);
        $contract->setStartAt(new DateTime());
        $contract->setFinishAt(new DateTime());
        $contract->setDueDate($today->format('j'));

        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'tenant11@example.com'
            )
        );

        $this->assertNotNull($tenant);
        $contract->setTenant($tenant);
        if ($isIntegrated) {
            $unitName = '1-a';
        } else {
            $unitName = 'HH-1';
        }

        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name'  => $unitName
            )
        );

        $this->assertNotNull($unit);
        /**
         * @var $group Group
         */
        $group = $unit->getGroup();
        $contract->setUnit($unit);
        $contract->setGroup($group);
        $contract->setHolding($unit->getHolding());
        $contract->setProperty($unit->getProperty());
        $contract->setStatus(ContractStatus::CURRENT);
        $em->persist($contract);
        $em->flush();
        $contractId = $contract->getId();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new ContractBalanceCommand());

        $command = $application->find('contract:update:balance');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );

        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);


        $this->assertEquals(999999.00, $contract->getBalance());
        if ($isIntegrated) {
            $this->assertEquals($contract->getRent(), $contract->getBalance());
        }

    }
}
