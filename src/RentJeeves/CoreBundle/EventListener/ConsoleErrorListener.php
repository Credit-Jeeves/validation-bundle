<?php

namespace RentJeeves\CoreBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Psr\Log\LoggerInterface;

/**
 * Class ConsoleErrorListener
 * @package RentJeeves\CoreBundle\EventListener
 */
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
     *
     * Check the exit code of every console command and send a system alert
     * See https://credit.atlassian.net/wiki/display/RT/System+Alerts
     *
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
        if ($this->shouldBeAlert($command)) {
            // this sends an alert email to our escalations team
            $this->logger->alert($logMessage);
        } else {
            // this just ends up in the log
            $this->logger->warning($logMessage);
        }
    }

    /**
     *
     * If this command fails should we send an alert or not?
     *
     * This is intended as a sort of "blacklist" so we can squelch non-critical alerts.
     *
     * @param $command
     * @return bool
     */
    private function shouldBeAlert($command)
    {
        // squelch alerts from this command
        if ($command->getName() == "payment:pay") {
            return false;
        }

        return true;
    }
}
