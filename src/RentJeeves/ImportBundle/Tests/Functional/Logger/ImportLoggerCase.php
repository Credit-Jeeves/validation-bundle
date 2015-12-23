<?php
namespace RentJeeves\TestBundle\Tests;

use Psr\Log\LoggerInterface;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ImportLoggerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function test1()
    {
        $this->getImportLogger()->warning('1234', ['group_id' => 2]);
        $this->getImportLogger1()->warning('5678');
    }

    /**
     * @return LoggerInterface
     */
    protected function getImportLogger()
    {
        return $this->getContainer()->get('monolog.logger.property_import');
    }
    /**
     * @return LoggerInterface
     */
    protected function getImportLogger1()
    {
        return $this->getContainer()->get('monolog.logger.import');
    }
}
