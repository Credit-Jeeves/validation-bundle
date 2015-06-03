<?php

namespace RentJeeves\CoreBundle\Tests\EventListener;

use RentJeeves\CoreBundle\EventListener\ConsoleErrorListener;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Session\Session;
use Psr\Log\LoggerInterface;

use RentJeeves\TestBundle\Stubs\LoggerStub;

class ConsoleErrorListenerCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeConstructedWithRightArguments()
    {
        new ConsoleErrorListener(new LoggerStub());
    }

    /**
     * @test
     */
    public function shouldNotLogOnConsoleZeroExitCode()
    {
        $event = $this->createTestEvent('some:command', 0);
        $loggerStub = new LoggerStub();

        $listener = new ConsoleErrorListener($loggerStub);
        $listener->onConsoleTerminate($event);

        $this->assertEquals(0, $loggerStub->getTotal('alert'), 'should not get alert if command exits with 0');
        $this->assertEquals(0, $loggerStub->getTotal('warning'), 'should not get alert if command exits with 0');
    }

    /**
     * @test
     */
    public function shouldAlertOnConsoleNonZeroExitCode()
    {
        $event = $this->createTestEvent('some:command', 1);
        $loggerStub = new LoggerStub();

        $listener = new ConsoleErrorListener($loggerStub);
        $listener->onConsoleTerminate($event);

        $this->assertEquals(1, $loggerStub->getTotal('alert'), 'should get alert if command exits with 1');
    }

    /**
     * @test
     */
    public function shouldWarndOnConsoleNonZeroExitCodeForPaymentPay()
    {
        $event = $this->createTestEvent('payment:pay', 1);
        $loggerStub = new LoggerStub();

        $listener = new ConsoleErrorListener($loggerStub);
        $listener->onConsoleTerminate($event);

        $this->assertEquals(0, $loggerStub->getTotal('alert'), 'should not get alert if payment:pay exits with 1');
        $this->assertEquals(1, $loggerStub->getTotal('warning'), 'should get warning if payment:pay exits with 1');
    }

    private function createTestEvent($name, $exitCode)
    {
        return new ConsoleTerminateEvent(
            new Command($name),
            $this->getMock('\Symfony\Component\Console\Input\InputInterface'),
            $this->getMock('\Symfony\Component\Console\Output\OutputInterface'),
            $exitCode
        );
    }
}
