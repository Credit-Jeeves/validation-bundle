<?php
namespace RentJeeves\DataBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
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
            array(28, '2014-02-28', 1000, 1000, '2014-03-28'),
            array(28, '2014-02-28', 1000, 0, '2014-02-28'),
            array(31, '2014-02-28', 1000, 1000, '2014-03-31'), //Fixed day
            array(2,  '2013-12-31', 1000, 1000, '2014-01-31'),
            array(31, '2013-12-31', 1000, 1000, '2014-01-31'),
            array(31, '2013-12-31', 950, 1000, '2014-01-31'),
            array(31, '2013-12-15', 550, 1000, '2013-12-31'),
            array(31, '2013-12-31', 500, 1000, '2014-01-15'),
            array(31, '2013-12-31', 500, 0, '2013-12-31'),
            array(1, '2014-01-01', 950, 1000, '2014-01-30'),
        );
    }

    /**
     * @test
     * @dataProvider providerShiftPaidTo
     */
    public function shiftPaidTo($dueDay, $paidTo, $amount, $rent, $result)
    {
        $contract = new Contract();
        $contract->setRent($rent);
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
            array(31, '2014-01-29', 967, '2013-12-31'),
            array(31, '2014-01-01', 567, '2013-12-15'),
            array(31, '2014-01-15', 500, '2013-12-31'),
            array(31, '2014-01-15', 400, '2013-12-31'),
            array(11, '2014-07-01', 836, '2014-06-06'),
        );
    }

    /**
     * @test
     * @dataProvider providerUnshiftPaidTo
     */
    public function unshiftPaidTo($dueDay, $paidTo, $amount, $result)
    {
        $contract = new Contract();
        $contract->setRent(1000);
        $contract->setDueDate($dueDay);
        $contract->setPaidTo(new DateTime($paidTo));
        $contract->unshiftPaidTo($amount);
        $this->assertEquals($result, $contract->getPaidTo()->format('Y-m-d'));
    }

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

    /**
     * @test
     */
    public function save()
    {
        $this->load(false);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'status' => ContractStatus::CURRENT,
                'rent' => 987
            )
        );

        $contract->setFinishAt(new DateTime('-2 years'));

        $errors = $this->getContainer()->get('validator')->validate($contract);
        $this->assertCount(1, $errors);
        $this->assertEquals('contract.error.is_end_later_than_start', $errors[0]->getMessage());
    }
}
