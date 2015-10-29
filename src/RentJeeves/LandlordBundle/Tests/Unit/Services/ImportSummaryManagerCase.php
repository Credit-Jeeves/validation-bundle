<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Services;

use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\LandlordBundle\Services\ImportSummaryManager;
use RentJeeves\TestBundle\BaseTestCase;

class ImportSummaryManagerCase extends BaseTestCase
{
    /**
     * @var ImportSummaryManager
     */
    protected $importSummaryManager;

    public function setUp()
    {
        $this->load(true);

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find('24');

        $this->importSummaryManager = $this->getContainer()->get('import_summary.manager');
        $this->importSummaryManager->initialize($group, ImportType::SINGLE_PROPERTY);
    }

    /**
     * @test
     */
    public function shouldSkipDuplicateExceptions()
    {
        $importErrors = $this->getEntityManager()->getRepository('RjDataBundle:ImportError')->findAll();
        $this->assertCount(0, $importErrors);

        $this->importSummaryManager->addException(['test' => 'test'], 'Test Massage', '5555aaaa4444');

        $importErrors = $this->getEntityManager()->getRepository('RjDataBundle:ImportError')->findAll();
        $this->assertCount(1, $importErrors);

        $this->importSummaryManager->addException(['test' => 'test'], 'Test Massage', '5555aaaa4444');
        $importErrors = $this->getEntityManager()->getRepository('RjDataBundle:ImportError')->findAll();
        $this->assertCount(1, $importErrors, 'Should stay 1 row b/c try to add duplicate exception');

        $this->importSummaryManager->addException(['test' => 'test1'], 'Test Massage', '5555aaaa4445');
        $importErrors = $this->getEntityManager()->getRepository('RjDataBundle:ImportError')->findAll();
        $this->assertCount(2, $importErrors);
    }
}
