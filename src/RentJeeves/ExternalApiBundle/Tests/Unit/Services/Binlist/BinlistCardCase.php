<?php

namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services\Binlist;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Entity\BinlistBank;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;
use RentJeeves\ExternalApiBundle\Services\Binlist\BinlistCard;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class BinlistCardCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnTrueIfCardIsDebitAndBankIsLowFee()
    {
        $bank = new BinlistBank();
        $bank->setLowDebitFee(true);
        $card = new DebitCardBinlist();
        $card->setBinlistBank($bank);

        $repository = $this->getRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['iin' => 123456, 'cardType' => BinlistCard::TYPE_DEBIT])
            ->will($this->returnValue($card));

        $binlistCard = new BinlistCard($repository);
        $this->assertTrue(
            $binlistCard->isLowDebitFee('1234567890000000'),
            'Should return true if card is DEBIT and bank uses low fee'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfCardIsDebitAndBankIsNotLowFee()
    {
        $bank = new BinlistBank();
        $bank->setLowDebitFee(false);
        $card = new DebitCardBinlist();
        $card->setBinlistBank($bank);

        $repository = $this->getRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['iin' => 123456, 'cardType' => BinlistCard::TYPE_DEBIT])
            ->will($this->returnValue($card));

        $binlistCard = new BinlistCard($repository);
        $this->assertFalse(
            $binlistCard->isLowDebitFee('1234567890000000'),
            'Should return false if card is DEBIT and bank does not use low fee'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfDebitCardWithGivenIinNotFound()
    {
        $repository = $this->getRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['iin' => 123456, 'cardType' => BinlistCard::TYPE_DEBIT])
            ->will($this->returnValue(null));

        $binlistCard = new BinlistCard($repository);
        $this->assertFalse(
            $binlistCard->isLowDebitFee('1234567890000000'),
            'Should return false if card is not found'
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfCardNumberHasLessThan6Chars()
    {
        $binlistCard = new BinlistCard($this->getRepositoryMock());
        $binlistCard->isLowDebitFee('101');
    }

    /**
     * @return EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        return $this->getMock('\Doctrine\ORM\EntityRepository', [], [], '', false);
    }
}
