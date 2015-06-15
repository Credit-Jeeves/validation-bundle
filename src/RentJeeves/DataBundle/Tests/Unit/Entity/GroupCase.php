<?php
namespace RentJeeves\DataBundle\Tests\Unit\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\TestBundle\BaseTestCase;

class GroupCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckSetterOrderAlgorithmWhenItIsCorrect()
    {
        $group = new Group();
        $this->assertEquals(OrderAlgorithmType::SUBMERCHANT, $group->getOrderAlgorithm());
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $this->assertEquals(OrderAlgorithmType::PAYDIRECT, $group->getOrderAlgorithm());
        $group->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $this->assertEquals(OrderAlgorithmType::SUBMERCHANT, $group->getOrderAlgorithm());

    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldCheckSetterOrderAlgorithmWhenItIsWrong()
    {
        $group = new Group();
        $group->setOrderAlgorithm(null);
    }
}
