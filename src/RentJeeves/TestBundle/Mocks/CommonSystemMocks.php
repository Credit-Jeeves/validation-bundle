<?php
namespace RentJeeves\TestBundle\Mocks;

class CommonSystemMocks extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    public function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher
     */
    public function getExceptionCatcherMock()
    {
        return $this->getMock('\Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\JMS\Serializer\Serializer
     */
    public function getSerializerMock()
    {
        return $this->getMock('\JMS\Serializer\Serializer', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\OrderSubmerchant
     */
    public function getOrderMock($orderId)
    {
        $orderMock = $this->getMock('\CreditJeeves\DataBundle\Entity\OrderSubmerchant', ["getId"], [], '', false);
        $orderMock->method('getId')
            ->will($this->returnValue($orderId));

        return $orderMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityManager
     */
    public function getEntityManagerMock()
    {
        return $this->getMock('\Doctrine\ORM\EntityManager', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\Holding
     */
    public function getHoldingMock()
    {
        $mockObj = $this->getMock('\CreditJeeves\DataBundle\Entity\Holding', [], [], '', false);

        return $mockObj;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroupMock()
    {
        $mockObj = $this->getMock('\CreditJeeves\DataBundle\Entity\Group', ['getHolding'], [], '', false);
        $mockObj->method('getHolding')
            ->will($this->returnValue($this->getHoldingMock()));

        return $mockObj;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\CoreBundle\Mailer\Mailer
     */
    public function getMailerMock()
    {
        return $this->getMock('\RentJeeves\CoreBundle\Mailer\Mailer', [], [], '', false);
    }
}
