<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Binlist;

use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\ExternalApiBundle\Services\BinlistSource;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;

class BinlistSourceCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnArrayCollectionOfEntity()
    {
        /** @var BinlistSource $binlistSource */
        $binlistSource = $this->getContainer()->get('binlist.source');
        /** @var ArrayCollection $collection */
        $collection = $binlistSource->getBinListCollection();
        $this->assertNotEmpty($collection, 'Didn\'t get collection');
        /** @var DebitCardBinlist $debitCardBinlist */
        $debitCardBinlist = reset($collection);
        $this->assertInstanceOf('RentJeeves\DataBundle\Entity\DebitCardBinlist', $debitCardBinlist);
        $this->assertEquals('341142', $debitCardBinlist->getIin());
        $this->assertEquals('AMEX', $debitCardBinlist->getCardBrand());
        $this->assertEquals('', $debitCardBinlist->getCardSubBrand());
        $this->assertEquals('CREDIT', $debitCardBinlist->getCardType());
        $this->assertEquals('', $debitCardBinlist->getCardCategory());
        $this->assertEquals('US', $debitCardBinlist->getCountryCode());
        $this->assertEquals('AMERICAN EXPRESS', $debitCardBinlist->getBankName());
        $this->assertEquals('www.americanexpress.com', $debitCardBinlist->getBankUrl());
        $this->assertEquals('', $debitCardBinlist->getBankPhone());
        $this->assertEquals('', $debitCardBinlist->getBankCity());
    }
}
