<?php
namespace CreditJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Operation;
use RentJeeves\TestBundle\BaseTestCase;
use DateTime;

class OrderCase extends BaseTestCase
{
    public static function dataProviderForDaysLate()
    {
        return array(
            array(new DateTime('2014-02-04'), new DateTime('2014-03-01'), 25),
            array(new DateTime('2014-03-01'), new DateTime('2014-02-04'), -25),
            array(new DateTime('2014-03-01'), new DateTime('2014-03-01'), 0),
            array(new DateTime('2014-03-01'), new DateTime('2014-03-02'), 1),
            array(new DateTime('2014-03-01'), new DateTime('2014-02-28'), -1),
        );
    }

    /**
     * @test
     * @dataProvider dataProviderForDaysLate
     */
    public function getDaysLate($createdAt, $paidFor, $result)
    {
        $operation = new Operation();
        $operation->setCreatedAt($createdAt);
        $operation->setPaidFor($paidFor);
        $this->assertEquals($result, $operation->getDaysLate());
    }
}
