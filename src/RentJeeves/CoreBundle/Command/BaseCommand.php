<?php
namespace RentJeeves\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @return \Symfony\Bridge\Monolog\Logger
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
}
