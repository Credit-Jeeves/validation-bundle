<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\Property;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SmartyStreetsAddressIndexingCommand extends BaseCommand
{
    const LIMIT = 100;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('smarty-streets-address-indexing')
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'ID of job')
            ->addOption('page', null, InputOption::VALUE_OPTIONAL, 'Number of page')
            ->setDescription('Indexing address for all property');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $page = $input->getOption('page');
        if ($page === null) {
            $this->createJobs();
        } else {
            $this->indexPage($page);
        }
    }

    protected function createJobs()
    {
        $pageAmount = ceil($this->countProperty() / self::LIMIT);
        for ($i = 1; $i <= $pageAmount; $i++) {
            $newJob = new Job($this->getName(), ['--page=' . $i]);
            $this->getEntityManager()->persist($newJob);
        }
        $this->getEntityManager()->flush();

        $this->getLogger()->debug(sprintf('Created %d jobs for index Property.', $pageAmount));
    }

    /**
     * @param int $page
     */
    protected function indexPage($page)
    {
        $this->getLogger()->debug('Start indexing page#' . $page);
        foreach ($this->getPropertiesByPage($page) as $property) {
            if ($property->getIndex() === null || $property->getLat() === null || $property->getLong()) {
                $this->indexProperty($property);
            }
        }
        $this->getEntityManager()->flush();
        $this->getLogger()->debug(sprintf('Page#%d indexed successfully.', $page));
    }

    /**
     * @param Property $property
     */
    protected function indexProperty(Property $property)
    {
        try {
            $address = $this->getSSLookupService()->lookup(
                $property->getAddress1(),
                $property->getCity(),
                $property->getArea(),
                $property->getZip()
            );
        } catch (AddressLookupException $e) {
            $this->getLogger()->error(
                sprintf(
                    'Cant index Property#%d : %s.',
                    $property->getId(),
                    $e->getMessage()
                )
            );

            return;
        }

        $property->setAddressFields($address);
    }

    /**
     * @return int
     */
    protected function countProperty()
    {
        return $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from('RjDataBundle:Property', 'p')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $page
     *
     * @return Property[]
     */
    protected function getPropertiesByPage($page)
    {
        $offset = ($page - 1) * self::LIMIT;

        return $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from('RjDataBundle:Property', 'p')
            ->setFirstResult($offset)
            ->setMaxResults(self::LIMIT)
            ->orderBy('p.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\AddressLookup\SmartyStreetsAddressLookupService
     */
    protected function getSSLookupService()
    {
        return $this->getContainer()->get('address_lookup_service.smarty_streets');
    }
}
