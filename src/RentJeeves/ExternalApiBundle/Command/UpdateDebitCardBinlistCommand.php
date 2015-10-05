<?php

namespace RentJeeves\ExternalApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;
use RentJeeves\ExternalApiBundle\Services\Binlist\BinlistSource;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDebitCardBinlistCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('api:binlist:update_data')
            ->setDescription('Remove all data from DebitCardBinList entity and fill it from source.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $connection = $em->getConnection();
        $connection->beginTransaction();
        $cmd = $em->getClassMetadata('RjDataBundle:DebitCardBinlist');

        try {
            $logger->info(sprintf('Start removing old data from table %s', $cmd->getTableName()));
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $connection->query(sprintf('DELETE FROM %s', $cmd->getTableName()));
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            $logger->alert(
                sprintf(
                    'Failed remove all data from table %s. Got exception %s',
                    $cmd->getTableName(),
                    $e->getMessage()
                )
            );

            return;
        }

        /** @var BinlistSource $binlistSource */
        $binlistSource = $this->getContainer()->get('binlist.source');
        $arrayCollection = $binlistSource->getBinListCollection();
        $logger->info(sprintf('Start inserting new data: %s', $arrayCollection->count()));
        foreach ($arrayCollection as $debitCardBinlist) {
            $em->persist($debitCardBinlist);
        }
        $em->flush();
        $logger->info('Successfully updated table.');
    }
}
