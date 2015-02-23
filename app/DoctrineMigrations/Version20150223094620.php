<?php

namespace Application\Migrations;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Monolog\Logger;

class Version20150223094620 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                ADD external_lease_id VARCHAR(255) DEFAULT NULL"
        );
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        //in live DB just one holding which must have this settings
        $holdings = $em->getRepository('DataBundle:Holding')->findHoldingsWithYardiSettings(0, 10);
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        $logger->addDebug(
            sprintf(
                "Migration - count holding which have yardi settings:%s",
                count($holdings)
            )
        );
        /** @var Holding $holding */
        foreach ($holdings as $holding) {
            $logger->addDebug(
                sprintf(
                    "Migration - process holding with ID:%s",
                    $holding->getId()
                )
            );
            $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
                ['holding' => $holding->getId()]
            );
            /** @var Contract $contract */
            foreach ($contracts as $contract) {
                $logger->addDebug(
                    sprintf(
                        "Migration - process contract with ID:%s",
                        $contract->getId()
                    )
                );
                $tenant = $contract->getTenant();
                $residentMapping = $tenant->getResidentForHolding($holding);
                if (empty($residentMapping)) {
                    $logger->addDebug(
                        sprintf(
                            "Migration - Don't have resident mapping for tenant:%s",
                            $tenant->getId()
                        )
                    );
                    continue;
                }
                $logger->addDebug(
                    sprintf(
                        "Migration - Setuped for contract:%s, residentId:%s",
                        $contract->getId(),
                        $residentMapping->getResidentId()
                    )
                );

                $contract->setExternalLeaseId($residentMapping->getResidentId());
                $em->persist($contract);
            }
        }
        $em->flush();
        $logger->addDebug("MIgration - make flush");
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );

        $this->addSql(
            "ALTER TABLE rj_contract_waiting
                DROP external_lease_id"
        );
    }
}
