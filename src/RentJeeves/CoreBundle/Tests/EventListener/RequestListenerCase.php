<?php

namespace RentJeeves\CoreBundle\Tests\EventListener;

use RentJeeves\CoreBundle\EventListener\RequestListener;
use Symfony\Component\HttpFoundation\Session\Session;

class RequestListenerCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Exception
     */
    public function shouldThrowExceptionWithoutArguments()
    {
        new RequestListener();
    }

    /**
     * @test
     */
    public function shouldBeConstructedWithRightArguments()
    {
        $context = $this->getContextMock();
        new RequestListener($context, new Session());
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    private function getContextMock()
    {
        return $this->getMock('\Symfony\Component\Security\Core\SecurityContext', [], [], '', false);
    }
}
