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

        $this->logger->alert(sprintf(
            'Command `%s` exited with status code %d',
            $command->getName(),
            $statusCode
        ));
    }
}
