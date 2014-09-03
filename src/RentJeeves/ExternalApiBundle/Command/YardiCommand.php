<?php
namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Soap\SoapClientEnum;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class YardiCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('yardi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Start");

        $clientFactory = $this->getContainer()->get('soap.client.factory');
        $yardiSettings = new YardiSettings();
        $yardiSettings->setUrl('https://www.iyardiasp.com/8223thirdparty708dev/');
        $yardiSettings->setUsername('renttrackws');
        $yardiSettings->setPassword('57742');
        $yardiSettings->setDatabaseName('afqoml_70dev');
        $yardiSettings->setDatabaseServer('sdb17\SQL2k8_R2');
        $yardiSettings->setPlatform('SQL Server');

        $resident = $clientFactory->getClient($yardiSettings, SoapClientEnum::RESIDENT);
        $resident->loginCheck();
    }
}
