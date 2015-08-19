<?php

namespace Application\Migrations;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\ExternalApiBundle\Services\MRI\ResidentDataManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;

class Version20160811074038 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $em;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $databaseName = $this->em->getConnection()->getDatabase();
        if (strripos($databaseName, 'jenkins') === true) {
            print_r(sprintf('Jenkins DB(%s), not run migration. %s', $databaseName, PHP_EOL));

            return;
        }

        $query = $this->em->getRepository('DataBundle:Holding')->getQueryForHoldingsWithMriSettings();
        $pageSize = 5;
        /** @var Paginator $paginator */
        $paginator  = new Paginator($query, $fetchJoinCollection = true);
        $totalItems = count($paginator);
        if ($totalItems === 0) {
            print_r(sprintf('We don\'t have any holding for migration %s', PHP_EOL));
        }
        $pagesCount = ceil($totalItems / $pageSize);
        print_r(sprintf('We have data for migration %s', PHP_EOL));
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
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }

    /**
     * @param Holding $holding
     */
    protected function migrateOneHolding(Holding $holding)
    {
        print_r(sprintf('Start migrate holding:%s%s', $holding->getId(), PHP_EOL));
        /** @var ResidentDataManager $residentManager */
        $residentManager = $this->container->get('mri.resident_data');
        $residentManager->setSettings($holding->getExternalSettings());
        $propertiesMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')
            ->getByHoldingAndGroupByExternalPropertyID($holding);
        /** @var PropertyMapping $propertyMapping */
        foreach ($propertiesMapping as $propertyMapping) {
            $residents = $residentManager->getResidents($propertyMapping->getExternalPropertyId());
            if (empty($residents)) {
                print_r(
                    sprintf(
                        'Can\'t find resident by propertyId:%s%s',
                        $propertyMapping->getExternalPropertyId(),
                        PHP_EOL
                    )
                );

                continue;
            }
            $this->migrateOneBatchResidents($residents, $holding);
            $nextPageLink = $residentManager->getNextPageLink();
            while (!empty($nextPageLink)) {
                $residents = $residentManager->getResidentsByNextPageLink($nextPageLink);
                if (empty($residents)) {
                    print_r(
                        sprintf(
                            'Can\'t find resident by nextPageLink:%s%s',
                            $nextPageLink,
                            PHP_EOL
                        )
                    );
                }
                $this->migrateOneBatchResidents($residents, $holding);
                $nextPageLink = $residentManager->getNextPageLink();
            }
        }
    }

    /**
     * @param array $residents
     * @param Holding $holding
     */
    protected function migrateOneBatchResidents(array $residents, Holding $holding)
    {
        foreach ($residents as $resident) {
            $this->migrateOneResident($resident, $holding);
        }
    }

    /**
     * @param Value $customer
     * @param Holding $holding
     */
    protected function migrateOneResident(Value $customer, Holding $holding)
    {
        print_r(sprintf('Start migrate customer, residentID:%s%s', $customer->getResidentId(), PHP_EOL));
        $currentExternalUnitId = sprintf('%s_%s', $customer->getBuildingId(), $customer->getUnitId());
        $newExternalUnitId = $customer->getExternalUnitId();
        $newUnitName = $customer->getUnitId();
        print_r(
            sprintf(
                'CurrentExternalUnitId:%s newExternalUnitId:%s, newUnitName: %s HoldingId:%s-%s %s',
                $currentExternalUnitId,
                $newExternalUnitId,
                $newUnitName,
                $holding->getId(),
                $holding->getName(),
                PHP_EOL
            )
        );
        try {
            $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')->getUnitMappingByHoldingAndExternalUnitId(
                $holding,
                $currentExternalUnitId
            );
        } catch (\Exception $e) {
            print_r(sprintf('Exception non unique result: %s. Please check DB, data not correct.', $e->getMessage()));
        }

        if (empty($unitMapping)) {
            print_r('Don\'t have unitMapping.');

            return;
        }
        print_r(
            sprintf(
                'We have unitMapping. Updating UnitMapping ID %s %s',
                $unitMapping->getId(),
                PHP_EOL
            )
        );
        $unitMapping->setExternalUnitId($newExternalUnitId);
        if ($unitMapping->getUnit()->getName() !== $newUnitName) {
            $unitMapping->getUnit()->setName($newUnitName);
        }

        $this->em->flush();
    }
}
