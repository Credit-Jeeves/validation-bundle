<?php
namespace RentJeeves\TestBundle\Command;

use ENC\Bundle\BackupRestoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use AppKernel;

class BackupCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('database:backup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        
        $factory = $container->get('backup_restore.factory');
        $directory = AppKernel::BACKUP_DIR_NAME;
        $fileName = AppKernel::BACKUP_FILE_NAME;
        
        $backupInstance = $factory->getBackupInstance('doctrine.dbal.default_connection');
        
        $backupPath = $backupInstance->backupDatabase($directory, $fileName);
        
        $output->writeln(
            sprintf('<comment>></comment> <info>Backup was successfully created in "%s".</info>', $backupPath)
        );
    }
}
