<?php
namespace RentJeeves\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Property;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PropertyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('property:duplicate')
            ->setDescription('Show duplicate property in DB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $propertyRepository = $doctrine->getRepository('RjDataBundle:Property');
        $iterableProperty = $propertyRepository->getPropetiesWhichDuplicate();

        foreach ($iterableProperty as $row) {
            $number = $row[0]['number'];
            $zip = $row[0]['zip'];
            $street = $row[0]['street'];
            $properties = $propertyRepository->findBy(
                array(
                    'zip'    => $zip,
                    'number' => $number,
                    'street' => $street,
                )
            );
            /**
             * @var $property Property
             */
            foreach ($properties as $property) {
                $output->writeln($property->getFullAddress().' || ID:'.$property->getId());
            }
        }
    }
}
