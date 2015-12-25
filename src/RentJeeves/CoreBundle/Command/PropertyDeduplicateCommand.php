<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\Exception\PropertyDeduplicatorException;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PropertyDeduplicateCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:property:dedupe')
            ->setDescription('Migrate units and contract from one property to another and remove dubbed Property')
            ->addOption('property-address-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, '', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->info('Start property deduplicating.');

        $addressId = $input->getOption('property-address-id');
        /** @var PropertyAddress $propertyAddress */
        if (null === $propertyAddress = $this->getPropertyAddressRepository()->find($addressId)) {
            throw new \InvalidArgumentException(
                sprintf('propertyAddress with id = %d not found.', $addressId)
            );
        }

        $propertyDeduplicator = $this->getPropertyDeduplicator();
        $propertyDeduplicator->setDryRunMode($input->getOption('dry-run'));

        try {
            $propertyDeduplicator->deduplicate($propertyAddress);
        } catch (PropertyDeduplicatorException $e) {
            $this->getLogger()->warning('Properties are not deduplicated: ' . $e);

            return;
        }

        $this->getLogger()->info('Properties are deduplicated.');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPropertyAddressRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress');
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\PropertyDeduplicator
     */
    protected function getPropertyDeduplicator()
    {
        return $this->getContainer()->get('dedupe.property_deduplicator');
    }
}
