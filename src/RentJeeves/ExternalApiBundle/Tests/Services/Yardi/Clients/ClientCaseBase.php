<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients;

use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ClientCaseBase extends BaseTestCase
{
    protected function getYardiSettings()
    {
        $yardiSettings = new YardiSettings();
        $yardiSettings->setUrl('https://www.iyardiasp.com/8223thirdparty708dev/');
        $yardiSettings->setUsername('renttrackws');
        $yardiSettings->setPassword('57742');
        $yardiSettings->setDatabaseName('afqoml_70dev');
        $yardiSettings->setDatabaseServer('sdb17\SQL2k8_R2');
        $yardiSettings->setPlatform('SQL Server');

        return $yardiSettings;
    }
}
