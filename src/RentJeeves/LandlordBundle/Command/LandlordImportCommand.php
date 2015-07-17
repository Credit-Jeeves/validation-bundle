<?php
namespace RentJeeves\LandlordBundle\Command;

use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LandlordImportCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('renttrack:landlord:import')
            ->setDescription('Import landlords, holdings, groups and properties from csv-file')
            ->addOption('partner-name', null, InputOption::VALUE_REQUIRED)
            ->addOption('path', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $partnerName = $input->getOption('partner-name');
        if (null === $partner = $this->getPartnerRepository()->findOneBy(['name' => $partnerName])) {
            throw new \InvalidArgumentException(sprintf('Partner with name \'%s\' not found', $partnerName));
        }

        $importer = $this->getLandlordCsvImporter();
        try {
            $importer->importPartnerLandlords($input->getOption('path'), $partner);
        } catch (\Exception $e) {
            $output->writeln(sprintf('[Landlord CSV import]: %s', $e->getMessage()));

            return;
        }

        foreach ($importer->getMappingErrors() as $errors) {
            $output->writeln(
                sprintf(
                    '<error>[Landlord CSV import]: %s for row :%s)</error>',
                    $errors['message'],
                    $errors['row']
                )
            );
        }
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPartnerRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:Partner');
    }

    /**
     * @return \RentJeeves\LandlordBundle\Accounting\ImportLandlord\LandlordCsvImporter
     */
    protected function getLandlordCsvImporter()
    {
        return $this->getContainer()->get('accounting.landlord_import.importer');
    }
}
