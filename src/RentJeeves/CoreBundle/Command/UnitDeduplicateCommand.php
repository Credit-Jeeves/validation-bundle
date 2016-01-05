<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\Exception\UnitDeduplicatorException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnitDeduplicateCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:unit:dedupe')
            ->setDescription('Migrate units and contract from one property to another')
            ->addOption('src-unit-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('dst-property-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, '', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->info('Start deduplicating.');

        if (null === $srcUnit = $this->getUnitRepository()->find($input->getOption('src-unit-id'))) {
            throw new \InvalidArgumentException(
                sprintf('Unit with id = %d not found.', $input->getOption('src-unit-id'))
            );
        }

        if (null === $dstProperty = $this->getPropertyRepository()->find($input->getOption('dst-property-id'))) {
            throw new \InvalidArgumentException(
                sprintf('Property with id = %d not found.', $input->getOption('dst-property-id'))
            );
        }

        $unitDeduplicator = $this->getUnitDeduplicator();
        $unitDeduplicator->setDryRunMode($input->getOption('dry-run'));

        try {
            $unitDeduplicator->deduplicate($srcUnit, $dstProperty);
        } catch (UnitDeduplicatorException $e) {
            $this->getLogger()->warning('Unit is not deduplicated: ' . $e);

            return;
        }

        $this->getLogger()->info('Unit is deduplicated.');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\UnitRepository
     */
    protected function getUnitRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:Unit');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\PropertyRepository
     */
    protected function getPropertyRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:Property');
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\UnitDeduplicator
     */
    protected function getUnitDeduplicator()
    {
        return $this->getContainer()->get('dedupe.unit_deduplicator');
    }
}
