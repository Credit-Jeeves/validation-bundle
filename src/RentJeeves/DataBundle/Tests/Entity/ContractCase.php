<?php
namespace RentJeeves\DataBundle\Tests\Entity;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\CoreBundle\DateTime;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class ContractCase extends BaseTestCase
{
    public function providerForgetStartAtWithDueDate()
    {
        $now = new DateTime();
        return array(
            array($now, $now->format('j'), $now),
            array(new DateTime('2001-01-01'), 3, new DateTime('2001-01-03')),
            array(new DateTime('2014-01-31'), 29, new DateTime('2014-02-28')),
        );
    }

    /**
     * @test
     * @dataProvider providerForgetStartAtWithDueDate
     */
    public function getStartAtWithDueDate($startAt, $dueDate, $result)
    {
        $contract = new Contract();
        $contract->setStartAt($startAt);
        $contract->setDueDate($dueDate);
        $this->assertEquals($result, $contract->getPaidToWithDueDate());
        $this->assertEquals($startAt, $contract->getStartAt());
    }
}
