<?php
namespace RentJeeves\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;

class ExceptionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('test:exception')
            ->setDescription('A command that creates an exception')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("About to throw a test exception.");
        throw new Exception("CONSOLE TEST EXCEPTION: This is only a test. Ignore me!!");
    }
}
