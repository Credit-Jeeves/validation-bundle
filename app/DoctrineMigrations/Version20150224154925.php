<?php

namespace Application\Migrations;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Model\Group;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Monolog\Logger;

class Version20150224154925 extends AbstractMigration implements ContainerAwareInterface
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
                        "Migration - Setup for contract:%s, residentId:%s",
                        $contract->getId(),
                        $residentMapping->getResidentId()
                    )
                );

                $contract->setExternalLeaseId($residentMapping->getResidentId());
                $em->persist($contract);
            }

            $groups = $holding->getGroups();
            /** @var Group $group */
            foreach ($groups as $group) {
                $waitingContracts = $group->getWaitingContracts();
                /** @var ContractWaiting $waitingContract */
                foreach ($waitingContracts as $waitingContract) {
                    $externalLeaseId = $waitingContract->getExternalLeaseId();
                    $residentId = $waitingContract->getResidentId();

                    if (!empty($externalLeaseId)) {
                        continue;
                    }
                    $logger->addDebug(
                        sprintf(
                            "Migration - Setup for contract waiting:%s, residentId:%s",
                            $waitingContract->getId(),
                            $residentId
                        )
                    );
                    $waitingContract->setExternalLeaseId($residentId);
                    $em->persist($waitingContract);
                }
            }
            $logger->addDebug("Migration - make flush");
        }

        $em->flush();
    }

    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != "mysql",
            "Migration can only be executed safely on 'mysql'."
        );
    }
}
