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
            array($now->format('Y-m-d'), $now->format('j'), $now->format('Y-m-d')),
            array('2001-01-01', 3, '2001-01-03'),
            array('2014-01-31', 29, '2014-02-28'),
        );
    }

    /**
     * @test
     * @dataProvider providerForgetStartAtWithDueDate
     */
    public function getStartAtWithDueDate($startAt, $dueDate, $result)
    {
        $contract = new Contract();
        $contract->setStartAt(new DateTime($startAt));
        $contract->setDueDate($dueDate);
        $this->assertEquals($result, $contract->getPaidToWithDueDate()->format('Y-m-d'));
        $this->assertEquals($startAt, $contract->getStartAt()->format('Y-m-d'));
    }

    public function providerShiftPaidTo()
    {
        return array(
            array(28, '2014-02-28', 1000, '2014-03-28'),
            array(31, '2014-02-28', 1000, '2014-03-31'), //Fixed day
            array(2,  '2013-12-31', 1000, '2014-01-31'),
            array(31, '2013-12-31', 1000, '2014-01-31'),
            array(31, '2013-12-31', 950, '2014-01-31'),
            array(31, '2013-12-31', 950, '2014-01-31', 1000),
            array(31, '2013-12-31', 950, '2014-01-31', -1000),
            array(31, '2013-12-31', 950, '2014-01-29', 950),
            array(31, '2013-12-31', 950, '2014-01-29', 50),
            array(31, '2013-12-31', 950, '2014-01-29', -50),
            array(31, '2013-12-31', 950, '2014-01-29', -950),
            array(31, '2013-12-15', 550, '2013-12-31'),
            array(31, '2013-12-31', 500, '2014-01-15'),
            array(1, '2014-01-01', 950, '2014-01-30'),
        );
    }

    /**
     * @test
     * @dataProvider providerShiftPaidTo
     */
    public function shiftPaidTo($dueDay, $paidTo, $amount, $result, $balance = 0)
    {
        $contract = new Contract();
        $contract->setRent(1000);
        $contract->setBalance($balance);
        $contract->setDueDate($dueDay);
        $contract->setPaidTo(new DateTime($paidTo));
        $contract->shiftPaidTo($amount);
        $this->assertEquals($result, $contract->getPaidTo()->format('Y-m-d'));
    }



    public function providerUnshiftPaidTo()
    {
        return array(
            array(28, '2014-03-28', 1000, '2014-02-28'),
            array(31, '2014-03-31', 1000, '2014-02-28'),
            array(31, '2014-03-31', 950, '2014-02-28'),
            array(2,  '2014-01-31', 1000, '2013-12-31'),
            array(31, '2014-01-31', 1000, '2013-12-31'),
            array(31, '2014-01-29', 950, '2013-12-31', 950),
            array(31, '2014-01-29', 950, '2013-12-31', 50),
            array(31, '2014-01-29', 950, '2013-12-31', -50),
            array(31, '2014-01-29', 950, '2013-12-31', -950),
            array(31, '2014-01-01', 550, '2013-12-15'),
            array(31, '2014-01-15', 500, '2013-12-31'),
            array(31, '2014-01-15', 500, '2013-12-31'),
            array(31, '2014-01-15', 400, '2013-12-31'),
        );
    }

    /**
     * @test
     * @dataProvider providerUnshiftPaidTo
     */
    public function unshiftPaidTo($dueDay, $paidTo, $amount, $result, $balance = 0)
    {
        $contract = new Contract();
        $contract->setRent(1000);
        $contract->setBalance($balance);
        $contract->setDueDate($dueDay);
        $contract->setPaidTo(new DateTime($paidTo));
        $contract->unshiftPaidTo($amount);
        $this->assertEquals($result, $contract->getPaidTo()->format('Y-m-d'));
    }
}
