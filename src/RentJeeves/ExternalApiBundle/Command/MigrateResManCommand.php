<?php
namespace RentJeeves\ExternalApiBundle\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\Collection;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResidentDataManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;

class MigrateResManCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ResidentDataManager
     */
    protected $residentManager;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected function configure()
    {
        $this->setName('external-api:resman:migrate-external-units');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->output = $output;
        $this->residentManager = $this->getContainer()->get('resman.resident_data');
        $this->executeMigration();
    }

    public function executeMigration()
    {
        $query = $this->em->getRepository('DataBundle:Holding')->getQueryForHoldingsWithResManSettings();
        $pageSize = 5;
        /** @var Paginator $paginator */
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $totalItems = count($paginator);
        if ($totalItems === 0) {
            $this->output->writeln('We don\'t have any holding for migration');
        }
        $pagesCount = ceil($totalItems / $pageSize);
        $this->output->writeln('We have data for migration');
        for ($i = 1; $pagesCount >= $i; $i++) {
            $paginator
                ->getQuery()
                ->setFirstResult($pageSize * ($i - 1))// set the offset
                ->setMaxResults($pageSize); // set the limit
            /** @var Holding $holding */
            foreach ($paginator as $holding) {
                $this->migrateOneHolding($holding);
            }
        }
    }

    /**
     * @param Holding $holding
     */
    protected function migrateOneHolding(Holding $holding)
    {
        $this->output->writeln(sprintf('Start migrate holding:%s', $holding->getId()));
        /** @var ResidentDataManager $residentManager */
        $this->residentManager->setSettings($holding->getExternalSettings());
        $propertiesMapping =  $holding->getPropertyMapping();
        /** @var PropertyMapping $propertyMapping */
        foreach ($propertiesMapping as $propertyMapping) {
            $residents = $this->residentManager->getResidents($propertyMapping->getExternalPropertyId());
            if (empty($residents)) {
                $this->output->writeln(
                    sprintf(
                        'Can\'t find resident by propertyId:%s',
                        $propertyMapping->getExternalPropertyId()
                    )
                );
                continue;
            }
            $this->migrateOneBatchResidents($residents, $holding, $propertyMapping);
        }
    }

    /**
     * @param array $residents
     * @param Holding $holding
     * @param Collection $groups
     */
    protected function migrateOneBatchResidents(array $residents, Holding $holding, PropertyMapping $propertyMapping)
    {
        /** @var RtCustomer $resident */
        foreach ($residents as $resident) {
            if ($resident->getCustomers()->getCustomer()->count() === 0) {
                continue;
            }

            /** @var Customer $customerUser */
            foreach ($resident->getCustomers()->getCustomer() as $customerUser) {
                $this->migrateOneResident($resident, $customerUser, $holding, $propertyMapping);
            }
        }
    }

    /**
     * @param RtCustomer $mainCustomer
     * @param Customer $customer
     * @param Holding $holding
     * @param Group $group
     */
    protected function migrateOneResident(
        RtCustomer $mainCustomer,
        Customer $customer,
        Holding $holding,
        PropertyMapping $propertyMapping
    ) {
        // externalPropertyIds are unique to groups (not holdings) so get the groups to scope lookup to
        if (null == $groups = $this->getGroupFromPropertyMapping($propertyMapping)) {
            return; // could not get a group
        }

        foreach ($groups as $group) {
            $this->output->writeln(
                sprintf(
                    'Start migrate customer, residentID:%s holdingId: %s Group %s',
                    $customer->getCustomerId(),
                    $holding->getId(),
                    $group->getId()
                )
            );
            $currentExternalUnitId = $mainCustomer->getRtUnit()->getUnitId();
            $newExternalUnitId = $customer->getExternalUnitId($mainCustomer);
            $this->output->writeln(
                sprintf(
                    'CurrentExternalUnitId:%s newExternalUnitId:%s',
                    $currentExternalUnitId,
                    $newExternalUnitId
                )
            );
            try {
                $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')->getMappingScopedByGroup(
                    $propertyMapping->getProperty(),
                    $group,
                    $currentExternalUnitId
                );
            } catch (\Exception $e) {
                $this->output->writeln(
                    sprintf(
                        'Exception:%s. Trace:%s. Please check data in DB.',
                        $e->getMessage(),
                        $e->getTraceAsString() // Because we are not getting message from doctrine exception RT-1491
                    )
                );

                return;
            }

            if (empty($unitMapping)) {
                $this->output->writeln('Can\'t find unitMapping');

                return;
            }
            $this->output->writeln('We found unitMapping and doing update with newExternalUnitId.');
            $unitMapping->setExternalUnitId($newExternalUnitId);
            $this->em->flush();
        }
    }

    /**
     * Get the group for the property.
     *
     * Write out warning and don't return a group if there is not at least one group found
     *
     * @param PropertyMapping $propertyMapping
     * @return Collection|null
     */
    protected function getGroupFromPropertyMapping(PropertyMapping $propertyMapping)
    {
        /** @var Group $group */
        $groups = $propertyMapping->getProperty()->getPropertyGroups();
        if (empty($groups)) {
            $this->output->writeln(
                sprintf(
                    'FAIL: Property ID:%s not associated with a Group',
                    $propertyMapping->getExternalPropertyId()
                )
            );

            return null;
        }

        if (count($groups) != 1) {
            $this->output->writeln(
                sprintf(
                    'WARNING: property ID:#%s has multiple groups. We will look for property mapping for each group.',
                    $propertyMapping->getExternalPropertyId()
                )
            );
        }

        return $groups;
    }
}
