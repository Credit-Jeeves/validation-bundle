<?php

namespace RentJeeves\TestBundle\Traits;

/**
 * Possible to use only in classes which implements \PHPUnit_Framework_TestCase
 *
 * @method \PHPUnit_Framework_MockObject_MockObject getMock
 */
trait CreateSystemMocksExtensionTrait
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Doctrine\ORM\EntityRepository
     */
    public function getEntityRepositoryMock()
    {
        return $this->getMock('Doctrine\ORM\EntityRepository', [], [], '', false);
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RentJeeves\CoreBundle\Session\Landlord
     */
    public function getSessionLandlordMock()
    {
        return $this->getMock('RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSessionMock()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Session\Session', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\CoreBundle\Mailer\Mailer
     */
    public function getMailerMock()
    {
        return $this->getMock('\RentJeeves\CoreBundle\Mailer\Mailer', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    public function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityManager
     */
    public function getEntityManagerMock()
    {
        return $this->getMock('\Doctrine\ORM\EntityManager', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Validator\Validator
     */
    public function getValidatorMock()
    {
        return $this->getMock('\Symfony\Component\Validator\Validator', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Validator\ConstraintViolation
     */
    public function getConstraintViolationMock()
    {
        return $this->getMock('\Symfony\Component\Validator\ConstraintViolation', [], [], '', false);
    }
}
