<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\CreateAciEnrollmentRequestCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class CreateAciEnrollmentRequestCommandCase extends BaseTestCase
{
    /**
     * remove created file
     */
    protected function tearDown()
    {
        parent::tearDown();
        if (true === file_exists($this->getFilePath())) {
            unlink($this->getFilePath());
        }
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return __DIR__ . '/test.csv';
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfSendNotCorrectHoldingId()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateAciEnrollmentRequestCommand());

        $command = $application->find('payment-processor:aci-import:create-enrollment-request');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
                '--holding_id' => 123456,
            ]
        );
    }

    /**
     * @test
     */
    public function shouldCreateFileWithRowsForHoldingIfSendHolding()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateAciEnrollmentRequestCommand());

        $command = $application->find('payment-processor:aci-import:create-enrollment-request');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
                '--holding_id' => 5,
            ]
        );

        $fileData = trim(file_get_contents($this->getFilePath()));
        $this->assertEquals(5, count(explode("\n", $fileData)));
    }

    /**
     * @test
     */
    public function shouldCreateFileWithRowsForAllHoldingIfNotSendHolding()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateAciEnrollmentRequestCommand());

        $command = $application->find('payment-processor:aci-import:create-enrollment-request');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
            ]
        );

        $fileData = trim(file_get_contents($this->getFilePath()));
        $this->assertEquals(7, count(explode("\n", $fileData)));// 5 for Holding#5 and 2 for other
    }

    /**
     * @return array
     */
    public function dataProviderForSeveralHoldings()
    {
        return [
            [5, 6, 7],
            [6, 7, 2],
        ];
    }

    /**
     * @param int $firstId
     * @param int $lastId
     * @param int $expectedCount
     *
     * @test
     * @dataProvider dataProviderForSeveralHoldings
     */
    public function shouldCreateFileWithRowsForRangeHoldings($firstId, $lastId, $expectedCount)
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateAciEnrollmentRequestCommand());

        $command = $application->find('payment-processor:aci-import:create-enrollment-request');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
                '--holding_id' => $firstId,
                '--holding_id_end' => $lastId,
            ]
        );

        $fileData = trim(file_get_contents($this->getFilePath()));
        $this->assertEquals($expectedCount, count(explode("\n", $fileData)));
    }

    /**
     * @test
     */
    public function shouldNotCreateFileForRangeHoldingsWithoutAciProfileMapForThisHoldings()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateAciEnrollmentRequestCommand());

        $command = $application->find('payment-processor:aci-import:create-enrollment-request');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
                '--holding_id' => 7,
                '--holding_id_end' => 15,
            ]
        );

        $this->assertFalse(file_exists($this->getFilePath()));
    }
}
