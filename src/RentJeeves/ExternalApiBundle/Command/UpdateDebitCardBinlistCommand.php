<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\ExternalApiBundle\Services\Binlist\BinlistSource;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\CoreBundle\Command\BaseCommand;

class UpdateDebitCardBinlistCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('api:binlist:update-data')
            ->setDescription('Remove all data from DebitCardBinList entity and fill it from source.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getLogger();
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $connection->beginTransaction();
        $cmd = $em->getClassMetadata('RjDataBundle:DebitCardBinlist');

        try {
            $logger->info(sprintf('Start removing old data from table %s', $cmd->getTableName()));
            $connection->query(sprintf('DELETE FROM %s', $cmd->getTableName()));
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
        $logger->info(sprintf('Start inserting data, should insert %s rows to DB.', count($arrayCollection)));
        try {
            foreach ($arrayCollection as $debitCardBinlist) {
                $em->persist($debitCardBinlist);
            }
            // Save entity
            $em->flush();
            // Try and commit the transaction
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            $logger->alert(
                sprintf(
                    'Failed save new data for Binlist. Got exception %s',
                    $e->getMessage()
                )
            );

            return;
        }
        $logger->info('Successfully saved new data.');
    }
}
