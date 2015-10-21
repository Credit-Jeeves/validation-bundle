<?php

namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services\Binlist;

use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\ExternalApiBundle\Services\Binlist\BinlistSource;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class BinlistSourceCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnArrayOfEntity()
    {
        /** @var BinlistSource $binlistSource */
        $binlistSource = $this->getContainer()->get('binlist.source');
        $data = $binlistSource->loadBinlistData();
        $this->assertNotEmpty($data, 'Can not load binlist data');
        $this->assertGreaterThan(5000, count($data), 'Count of Binlist data less than expected');
        $debitCardData = reset($data);
        $this->assertNotEmpty($debitCardData['iin'], 'Iin not found');
        $this->assertNotEmpty($debitCardData['bank_name'], 'bank_name not found');
    }
}
