<?php
namespace RentJeeves\CoreBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->getContainer()->get('logger');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Method should use for monitoring memory usage
     */
    protected function printMemoryUsage()
    {
        $this->getLogger()->info(
            sprintf(
                'Memory usage (currently) %dKB/ (max) %dKB',
                round(memory_get_usage(true) / 1024),
                memory_get_peak_usage(true) / 1024
            )
        );
    }
}
