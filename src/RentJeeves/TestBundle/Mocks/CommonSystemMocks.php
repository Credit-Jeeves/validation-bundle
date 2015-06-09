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
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\Order
     */
    public function getOrderMock($orderId)
    {
        $orderMock = $this->getMock('\CreditJeeves\DataBundle\Entity\Order', ["getId"], [], '', false);
        $orderMock->method('getId')
            ->will($this->returnValue($orderId));

        return $orderMock;
    }
}
