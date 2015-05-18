<?php

namespace RentJeeves\DataBundle\Tests\EventListener;

use RentJeeves\DataBundle\Entity\ImportSummary;
use RentJeeves\TestBundle\BaseTestCase as Base;

class ImportSummaryListenerCase extends Base
{
    /**
     * @test
     */
    public function shouldSetupPulicId()
    {
        $importSummary = new ImportSummary();
        $em = $this->getEntityManager();
        $em->persist($importSummary);
        $em->flush($importSummary);
        $this->assertNotEmpty($importSummary->getPublicId(), 'Public ID must be setted');
    }
}
