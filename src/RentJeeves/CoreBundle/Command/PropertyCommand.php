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
    /**
     * @var string
     */
    const ONLY_WITH_CONTRACT = 'only-with-contract';

    /**
     * var boolean
     */
    const ONLY_WITH_CONTRACT_DEFAULT = false;

    protected function configure()
    {
        $this
            ->setName('property:duplicate')
            ->setDescription('Show duplicate property in DB')
            ->addOption(
                self::ONLY_WITH_CONTRACT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Only property which have contract, can set 1 or 0',
                self::ONLY_WITH_CONTRACT_DEFAULT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $onlyWithContract = $input->getOption(self::ONLY_WITH_CONTRACT);
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
