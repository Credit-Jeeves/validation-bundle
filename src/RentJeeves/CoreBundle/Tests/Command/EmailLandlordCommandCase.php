<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CoreBundle\Command\EmailLandlordCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class EmailLandlordCommandCase extends BaseTestCase
{
    protected function tearDown()
    {
        $this->rollbackTransaction();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testExecute()
    {
        $this->load(true);
        $this->startTransaction();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );
        $this->assertRegExp('/Story-1555/', $commandTester->getDisplay());
    }

    /**
     * Story-2042
     * Contracts with status="pending"
     * @test
     */
    public function executePending()
    {
        $this->load();
        $this->startTransaction();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'pending'
            )
        );
        $this->assertCount(4, $plugin->getPreSendMessages());
        $this->assertRegExp('/Story-2042/', $commandTester->getDisplay());
    }

    /**
     * Story-1555
     * @test
     */
    public function executePaid()
    {
        $this->load();
        $this->startTransaction();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'paid'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(0, $count);

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('ivan@rentrack.com');
        // this tenant has only one order
        $order = $tenant->getOrders()->first();
        $order->setStatus(OrderStatus::PENDING);
        $em->flush($order);
        $plugin->clean();

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'paid'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(1, $count);
        $this->assertRegExp('/Story-1555/', $commandTester->getDisplay());
    }

    /**
     * Story-1560
     * @test
     */
    public function executeNotPaid()
    {
        $this->load();
        $this->startTransaction();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'nsf'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(1, $count);
        $this->assertRegExp('/Story-1560/', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function executeReport()
    {
        $this->load();
        $this->startTransaction();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'report'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(3, $count); // 1 user has `emailNotification=false`
        $this->assertRegExp('/daily report/', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function executeLateTenants()
    {
        $this->load();
        $this->startTransaction();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new EmailLandlordCommand());
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        $command = $application->find('Email:landlord');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--type' => 'late'
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(1, $count);
        $this->assertRegExp('/Late contracts/', $commandTester->getDisplay());
    }
}
