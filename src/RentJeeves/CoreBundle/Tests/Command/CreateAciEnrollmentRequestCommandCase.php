<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\CreateAciEnrollmentRequestCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CreateAciEnrollmentRequestCommandCase extends BaseTestCase
{
    /**
     * remove created file
     */
    protected function tearDown()
    {
        parent::tearDown();
        if (true === file_exists($this->getDirPath())) {
            $fileSystem = new Filesystem();
            $fileSystem->remove($this->getDirPath());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (false === is_dir($this->getDirPath())) {
            mkdir($this->getDirPath());
        }
    }

    /**
     * @return string
     */
    protected function getDirPath()
    {
        return __DIR__ . '/../Fixtures/ExportFiles';
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Option "prefix" cannot be NULL.
     */
    public function shouldThrowExceptionIfNotSendPrefix()
    {
        $this->executeCommandTester(
            new CreateAciEnrollmentRequestCommand(),
            []
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Option "path" should contain path to writable directory.
     */
    public function shouldThrowExceptionIfNotSendPathToDir()
    {
        $this->executeCommandTester(
            new CreateAciEnrollmentRequestCommand(),
            [
                '--prefix' => 'test',
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Holding with id#123456 not found
     */
    public function shouldThrowExceptionIfSendNotCorrectHoldingId()
    {
        $this->executeCommandTester(
            new CreateAciEnrollmentRequestCommand(),
            [
                '--path' => $this->getDirPath(),
                '--prefix' => 'test',
                '--holding_id' => 123456,
            ]
        );
    }

    /**
     * @test
     */
    public function shouldCreateOneFileWithRowsForHoldingIfSendHoldingAndNotSendLimit()
    {
        $this->executeCommandTester(
            new CreateAciEnrollmentRequestCommand(),
            [
                '--path' => $this->getDirPath(),
                '--prefix' => 'test',
                '--holding_id' => 5,
            ]
        );

        $finder = new Finder();
        $finder->in($this->getDirPath())->files()->name('*.csv');
        $this->assertCount(1, $finder, 'Should be created only 1 file.');

        $fileData = trim(file_get_contents($finder->getIterator()->getRealPath()));
        $this->assertEquals(5, count(explode("\n", $fileData)), 'File should contain 5 rows');
    }

    /**
     * @test
     */
    public function shouldCreateTwoFilesWithRowsForHoldingIfSendHoldingAndLimit()
    {
        $this->executeCommandTester(
            new CreateAciEnrollmentRequestCommand(),
            [
                '--path' => $this->getDirPath(),
                '--prefix' => 'test',
                '--holding_id' => 5,
                '--profiles' => 1,
            ]
        );

        $finder = new Finder();
        $finder->in($this->getDirPath())->files()->name('*.csv');
        $this->assertCount(2, $finder, 'Should be created 2 files.');
    }

    /**
     * @test
     */
    public function shouldCreateFileWithRowsForAllHoldingIfNotSendHolding()
    {
        $this->executeCommandTester(
            new CreateAciEnrollmentRequestCommand(),
            [
                '--path' => $this->getDirPath(),
                '--prefix' => 'test',
            ]
        );

        $finder = new Finder();
        $finder->in($this->getDirPath())->files()->name('*.csv');
        $this->assertCount(1, $finder, 'Should be created only 1 file.');

        $fileData = trim(file_get_contents($finder->getIterator()->getRealPath()));
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
        $this->executeCommandTester(
            new CreateAciEnrollmentRequestCommand(),
            [
                '--path' => $this->getDirPath(),
                '--holding_id' => $firstId,
                '--holding_id_end' => $lastId,
                '--prefix' => 'test',
            ]
        );
        $finder = new Finder();
        $finder->in($this->getDirPath())->files()->name('*.csv');
        $this->assertCount(1, $finder, 'Should be created only 1 file.');

        $fileData = trim(file_get_contents($finder->getIterator()->getRealPath()));
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
                '--path' => $this->getDirPath(),
                '--holding_id' => 7,
                '--holding_id_end' => 15,
                '--prefix' => 'test',
            ]
        );

        $finder = new Finder();
        $finder->in($this->getDirPath())->files()->name('*.csv');
        $this->assertCount(0, $finder, 'Should not be created any files.');
    }
}
