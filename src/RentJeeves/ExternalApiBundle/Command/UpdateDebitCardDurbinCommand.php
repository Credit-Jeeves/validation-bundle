<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class UpdateDebitCardDurbinCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('api:durbin:update-data')
            ->setDescription('Remove all data from DebitCardDurbin entity and fill it from source.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Logger $logger */
        $logger = $this->getLogger();
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $connection->beginTransaction();
        $tableName = $em->getClassMetadata('RjDataBundle:DebitCardDurbin')->getTableName();

        try {
            $logger->info(sprintf('Start removing old data from table %s', $tableName));
            $connection->query(sprintf('DELETE FROM %s', $tableName));
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            $logger->alert(
                sprintf(
                    'Failed remove all data from table %s. Got exception %s',
                    $tableName,
                    $e->getMessage()
                )
            );

            return;
        }

        $this->finder = new Finder();
        $this->finder->in(__DIR__.'/../../../../data/durbin');
        $csvFiles = $this->finder->files()->name('*.csv');
        $connection->beginTransaction();

        try {
            /** @var CsvFileReader $csvReader */
            $csvReader = $this->getContainer()->get('reader.csv');
            $csvReader->setUseHeader(false);
            /** @var SplFileInfo $file */
            foreach ($csvFiles as $file) {
                $pathName = realpath($file->getPathname());
                $result = $csvReader->read($pathName);
                foreach ($result as $values) {
                    $query = 'INSERT INTO ' . $tableName;
                    $query .= ' (`frb_id`, `short_name`, `city`, `state`, `type`, `fdic_id`, `ots_id`, `ncua_id`)';
                    $query .= "VALUES ( '" . implode("' , '", $values) . "' )";
                    $connection->query($query);
                }
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            $logger->alert(
                sprintf(
                    'Failed insert all data to table %s. Got exception %s',
                    $tableName,
                    $e->getMessage()
                )
            );

            return;
        }
    }
}
