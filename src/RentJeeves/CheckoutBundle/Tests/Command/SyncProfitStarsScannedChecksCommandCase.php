<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\CheckoutBundle\Command\SyncProfitStarsScannedChecksCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class SyncProfitStarsScannedChecksCommandCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Group with id#9999 not found
     */
    public function shouldTrowExceptionIfGroupNotFound()
    {
        $this->load(true);
        $command = new SyncProfitStarsScannedChecksCommand();

        $this->executeCommandTester(
            $command,
            [
                '--group-id' => 9999
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Date 1 March is incorrect
     */
    public function shouldTrowExceptionIfIncorrectDatePassed()
    {
        $command = new SyncProfitStarsScannedChecksCommand();

        $this->executeCommandTester(
            $command,
            [
                '--date' => '1 March'
            ]
        );
    }

    /**
     * @test
     */
    public function shouldCreateProfitStarsBatchWhenGroupIdAndDateOptionsPassed()
    {

    }
}
