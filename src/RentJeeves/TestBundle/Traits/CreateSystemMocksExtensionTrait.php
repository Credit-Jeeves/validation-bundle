<?php

namespace RentJeeves\TestBundle\Traits;

/**
 * Possible to use only in classes which implements \PHPUnit_Framework_TestCase
 */
trait CreateSystemMocksExtensionTrait
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityRepository
     */
    public function getEntityRepositoryMock()
    {
        return $this->getBaseMock('Doctrine\ORM\EntityRepository');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\CoreBundle\Session\Landlord
     */
    public function getSessionLandlordMock()
    {
        return $this->getBaseMock('RentJeeves\CoreBundle\Session\Landlord');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSessionMock()
    {
        return $this->getBaseMock('Symfony\Component\HttpFoundation\Session\Session');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\CoreBundle\Mailer\Mailer
     */
    public function getMailerMock()
    {
        return $this->getBaseMock('\RentJeeves\CoreBundle\Mailer\Mailer');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    public function getLoggerMock()
    {
        return $this->getBaseMock('\Monolog\Logger');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityManager
     */
    public function getEntityManagerMock()
    {
        return $this->getBaseMock('\Doctrine\ORM\EntityManager');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Validator\Validator
     */
    public function getValidatorMock()
    {
        return $this->getBaseMock('\Symfony\Component\Validator\Validator');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Validator\ConstraintViolation
     */
    public function getConstraintViolationMock()
    {
        return $this->getBaseMock('\Symfony\Component\Validator\ConstraintViolation');
    }

    /**
     * @param string $class Class name with namespace
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getBaseMock($class)
    {
        return $this->getMock($class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\JMS\Serializer\Serializer
     */
    public function getSerializerMock()
    {
        return $this->getMock('\JMS\Serializer\Serializer', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher
     */
    public function getExceptionCatcherMock()
    {
        return $this->getMock('\Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher', [], [], '', false);
    }
}
