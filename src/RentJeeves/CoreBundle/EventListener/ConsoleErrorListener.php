<?php

namespace RentJeeves\CoreBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Psr\Log\LoggerInterface;

class ConsoleErrorListener
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        $statusCode = $event->getExitCode();
        $command = $event->getCommand();

        if ($statusCode === 0) {
            return;
        }

        if ($statusCode > 255) {
            $statusCode = 255;
            $event->setExitCode($statusCode);
        }

        $logMessage = sprintf('Command `%s` exited with status code %d', $command->getName(), $statusCode);
        if ($this->shouldBeAlert($command))
        {
            // this sends an alert email to our escalations team
            $this->logger->alert($logMessage);
        } else {
            // this just ends up in the log
            $this->logger->warning($logMessage);
        }
    }

    private function shouldBeAlert($command)
    {
        // squelch alerts from this command
        if ($command->getName() == "payment:pay") {

            return false;
        }

        return true;
    }
}
