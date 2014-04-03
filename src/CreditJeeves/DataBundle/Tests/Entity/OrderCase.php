<?php
namespace CreditJeeves\DataBundle\Tests\Entity;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use RentJeeves\TestBundle\BaseTestCase;
use DateTime;

class OrderCase extends BaseTestCase
{
    /**
     * @test
     */
    public function getPostMonth()
    {
        $order = new Order();

        $operationRent = new Operation();
        $operationRent->setType(OperationType::RENT);
        $operationRent->setPaidFor(new DateTime('2014-02-04'));
        $order->addOperation($operationRent);

        $operationOther = new Operation();
        $operationOther->setType(OperationType::OTHER);
        $order->addOperation($operationOther);

        $this->assertEquals('2014-02-04T00:02:2', $order->getPostMonth());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Order has more than ONE 'RENT' operation
     */
    public function getPostMonthException()
    {
        $order = new Order();

        $operationRent = new Operation();
        $operationRent->setType(OperationType::RENT);
        $order->addOperation($operationRent);

        $operationRent2 = new Operation();
        $operationRent2->setType(OperationType::RENT);
        $order->addOperation($operationRent2);

        $order->getPostMonth();
    }

    /**
     * @test
     */
    public function getPostMonthException2()
    {
        $order = new Order();

        $operationOther = new Operation();
        $operationOther->setType(OperationType::OTHER);
        $order->addOperation($operationOther);

        $this->assertEquals('', $order->getPostMonth());
    }
}
