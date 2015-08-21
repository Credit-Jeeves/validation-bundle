<?php
namespace RentJeeves\ExternalApiBundle\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
        $propertiesMapping =  $this->em->getRepository('RjDataBundle:PropertyMapping')
            ->getByHoldingAndGroupByExternalPropertyID($holding);
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
            $this->migrateOneBatchResidents($residents, $holding);
        }
    }

    /**
     * @param array $residents
     * @param Holding $holding
     */
    protected function migrateOneBatchResidents(array $residents, Holding $holding)
    {
        /** @var RtCustomer $resident */
        foreach ($residents as $resident) {
            if ($resident->getCustomers()->getCustomer()->count() === 0) {
                continue;
            }

            /** @var Customer $customerUser */
            foreach ($resident->getCustomers()->getCustomer() as $customerUser) {
                $this->migrateOneResident($resident, $customerUser, $holding);
            }
        }
    }

    /**
     * @param RtCustomer $mainCustomer
     * @param Customer $customer
     * @param Holding $holding
     */
    protected function migrateOneResident(RtCustomer $mainCustomer, Customer $customer, Holding $holding)
    {
        $this->output->writeln(
            sprintf(
                'Start migrate customer, residentID:%s holdingId: %s',
                $customer->getCustomerId(),
                $holding->getId()
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
            $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')->getUnitMappingByHoldingAndExternalUnitId(
                $holding,
                $currentExternalUnitId
            );
        } catch (\Exception $e) {
            $this->output->writeln(sprintf('Exception:%s. Please check data in DB.', $e->getMessage()));

            return;
        }

        if (empty($unitMapping)) {
            $this->output->writeln('Can\'t find unitMapping');

            return;
        }
        $this->output->writeln('We find unitMapping and doing update with newExternalUnitId.');
        $unitMapping->setExternalUnitId($newExternalUnitId);
        $this->em->flush();
    }
}
