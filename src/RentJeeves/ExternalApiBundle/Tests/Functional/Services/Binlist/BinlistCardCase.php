<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional\Services\Binlist;

use RentJeeves\DataBundle\Entity\BinlistBank;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;
use RentJeeves\ExternalApiBundle\Services\Binlist\BinlistCard;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class BinlistCardCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnTrueIfCardIsDebitAndBankIsLowFeeAndIinHas8Chars()
    {
        $this->load(true);
        $bank = new BinlistBank();
        $bank->setBankName('ABC');
        $bank->setLowDebitFee(true);
        $card = new DebitCardBinlist();
        $card->setBinlistBank($bank);
        $card->setIin('12345622');
        $card->setCardType(BinlistCard::TYPE_DEBIT);

        $this->getEntityManager()->persist($card);
        $this->getEntityManager()->flush($card);

        $this->assertTrue(
            $this->getContainer()->get('binlist.card')->isLowDebitFee('1234560000000'),
            'Should find debit card by first 6 chars and return is_low_debit_fee=true'
        );
    }
}
