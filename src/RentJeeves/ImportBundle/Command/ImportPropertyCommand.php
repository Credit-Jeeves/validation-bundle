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
            ->addOption('external-property-id', null, InputOption::VALUE_REQUIRED, 'External Property ID')
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

        $externalPropertyId = $input->getOption('external-property-id');
        $propertyMappings = $this->getEntityManager()->getRepository('RjDataBundle:PropertyMapping')
            ->getPropertyMappingByGroupAndExternalPropertyId($import->getGroup(), $externalPropertyId);
        if (null == $propertyMappings) {
            throw new \InvalidArgumentException(
                sprintf(
                    'PropertyMapping for Group#%d and extPropertyId#%s not found',
                    $import->getGroup()->getId(),
                    $externalPropertyId
                )
            );
        }

        $this->getImportPropertyManager()->import($import, $input->getOption('external-property-id'));
    }

    /**
     * @return \RentJeeves\ImportBundle\PropertyImport\ImportPropertyManager
     */
    protected function getImportPropertyManager()
    {
        return $this->getContainer()->get('import.property.manager');
    }
}
