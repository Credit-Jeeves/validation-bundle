<?php

namespace RentJeeves\ImportBundle\Command;

use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPropertyCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:import:property')
            ->addOption('external-property-id', null, InputOption::VALUE_OPTIONAL, 'External Property ID')
            ->addOption('path-to-file', null, InputOption::VALUE_OPTIONAL, 'Path to CSV-file')
            ->addOption('import-id', null, InputOption::VALUE_REQUIRED, 'Import ID')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'Job ID')
            ->setDescription('Import Properties for Group.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importId = $input->getOption('import-id');
        if (null == $import = $this->getEntityManager()->getRepository('RjDataBundle:Import')->find($importId)) {
            throw new \InvalidArgumentException(sprintf('Entity Import#%s not found', $importId));
        }

        $pathToFile = $input->getOption('path-to-file');
        $extPropertyId = $input->getOption('external-property-id');

        if ($pathToFile && $extPropertyId) {
            throw new \InvalidArgumentException(
                'Both options("path-to-file" and "external-property-id") are specified'
            );
        }

        if (false == $pathToFile && false == $extPropertyId) {
            throw new \InvalidArgumentException(
                'Neither option is specified. Pls specify option "path-to-file" or "external-property-id"'
            );
        }

        $this->getImportPropertyManager()->import($import, $pathToFile ?: $extPropertyId);
    }

    /**
     * @return \RentJeeves\ImportBundle\PropertyImport\ImportPropertyManager
     */
    protected function getImportPropertyManager()
    {
        return $this->getContainer()->get('import.property.manager');
    }
}
