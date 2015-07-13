<?php

namespace RentJeeves\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MapAciImportProfileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('aci:import-profile:map')
            ->setDescription('Populates users and groups to rj_aci_import_profile table');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting populating ACI import profile data...');

        try {
            /** @var EntityManager $em */
            $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
            $connection = $em->getConnection();

            $queryGroup = '
                insert into rj_aci_import_profile_map (group_id)
                select distinct g.id
                from rj_group g inner join rj_billing_account ba on (g.id = ba.group_id)
                left join rj_aci_import_profile_map m on (g.id = m.group_id)
                where m.id is NULL';
            $stmt = $connection->prepare($queryGroup);
            $stmt->execute();

            $queryUser = '
                insert into rj_aci_import_profile_map (user_id)
                select distinct u.id
                from cj_user u inner join rj_payment_account pa on (u.id = pa.user_id and u.type = "tenant")
                left join rj_aci_import_profile_map m on (u.id = m.user_id)
                where m.id is NULL';
            $stmt = $connection->prepare($queryUser);
            $stmt->execute();
            $output->writeln('ACI Import data has been populated successfully!');
        } catch (\Exception $e) {
            $output->writeln(sprintf('Something went wrong. %s', $e->getMessage()));
        }
    }
}
