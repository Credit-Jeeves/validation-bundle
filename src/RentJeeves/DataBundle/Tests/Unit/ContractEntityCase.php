<?php

namespace RentJeeves\DataBundle\Tests\Unit;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\TestBundle\BaseTestCase;
use \DateTime;

class ContractEntityCase extends BaseTestCase
{
    /**
     * @test
     */
    public function contractNotLate()
    {
        $contract = new Contract();
        $date = new DateTime();
        $date->modify('+1 day');
        $contract->setPaidTo($date);

        $this->assertFalse($contract->isLate());
    }

    /**
     * @test
     */
    public function contractLate()
    {
        $contract = new Contract();
        $date = new DateTime();
        $date->modify('-1 day');
        $contract->setPaidTo($date);

        $this->assertTrue($contract->isLate());
    }
}
