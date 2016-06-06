<?php
namespace RentJeeves\TestBundle\Tests\Functional\FixtureGenerator\Command;

use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\FixtureGenerator\Command\LoadDataFixturesCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class LoadDataFixturesCommandCase extends BaseTestCase
{
    protected $commandName = 'renttrack:alice-fixtures:load';
    protected $pathWithTestFixtures = '@RjTestBundle/Tests/Fixtures/FixtureGenerator/Services/';

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Please, set --path for loading
     */
    public function shouldThrowExceptionIfSetInvalidOptions()
    {
        $this->executeCommandTester(new LoadDataFixturesCommand());
    }

    /**
     * @test
     */
    public function shouldLoadedAllFixturesFromPath()
    {
        $this->load(true);

        $commandTester = $this->getCommandTester();

        $commandTester->execute([
            'command' => $this->commandName,
            '--path' => $this->pathWithTestFixtures
        ]);

        $em = $this->getEntityManager();

        $this->assertRegExp('/Total count: 2/', $commandTester->getDisplay());
        /** @var Landlord $newUser */
        $newUser = $em->getRepository('RjDataBundle:Landlord')->findOneBy(['email' => 'landloard_petr@example.com']);
        $this->assertNotNull($newUser, 'User should exist in DB');
        $this->assertEquals('TestNameHolding', $newUser->getHolding(), 'Holding names should be equal');
    }

    /**
     * @test
     */
    public function shouldLoadDataIfSetFile()
    {
        $this->load(true);

        $commandTester = $this->getCommandTester();

        $commandTester->execute([
            'command' => $this->commandName,
            '--path' => $this->pathWithTestFixtures,
            '--files' => ['test.yml'],
        ]);

        $em = $this->getEntityManager();

        $this->assertRegExp('/Total count: 2/', $commandTester->getDisplay());
        /** @var Landlord $newUser */
        $newUser = $em->getRepository('RjDataBundle:Landlord')->findOneBy(['email' => 'landloard_petr@example.com']);
        $this->assertNotNull($newUser, 'User should exist in DB');
        $this->assertEquals('TestNameHolding', $newUser->getHolding(), 'Holding names should be equal');
    }

    /**
     * @return CommandTester
     */
    protected function getCommandTester()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $command = new LoadDataFixturesCommand();
        $application->add($command);

        $command = $application->find($command->getName());
        $commandTester = new CommandTester($command);

        return $commandTester;
    }
}
