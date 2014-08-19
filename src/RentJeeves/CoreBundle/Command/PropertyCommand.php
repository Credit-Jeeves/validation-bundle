<?php
namespace RentJeeves\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Property;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PropertyCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    const OPTION_ONLY_WITH_CONTRACT = 'only-with-contract';

    protected function configure()
    {
        $this
            ->setName('property:duplicate')
            ->setDescription('Show duplicate property in DB')
            ->addOption(
                self::OPTION_ONLY_WITH_CONTRACT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Show duplicate property which have contract.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $onlyWithContract = $input->getOption(self::OPTION_ONLY_WITH_CONTRACT);
        $doctrine = $this->getContainer()->get('doctrine');
        $propertyRepository = $doctrine->getRepository('RjDataBundle:Property');
        if ($onlyWithContract) {
            $properties = $propertyRepository->getDublicatePropertiesWithContract();
        } else {
            $properties = $propertyRepository->getDuplicateProperties();
        }
        foreach ($properties as $row) {
            $number = $row['number'];
            $zip = $row['zip'];
            $street = $row['street'];
            $contractId = (isset($row['contract_id']))? $row['contract_id']: null;
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
                $message = $property->getFullAddress().' || PROPERTY_ID:'.$property->getId();
                if ($onlyWithContract) {
                    $message .= ' CONTRACT_ID:'.$contractId;
                }

                $output->writeln($message);
            }
        }
    }
}
