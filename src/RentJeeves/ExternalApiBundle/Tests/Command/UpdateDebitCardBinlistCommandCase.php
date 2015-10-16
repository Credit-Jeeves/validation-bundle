<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Entity\BinlistBank;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;
use RentJeeves\ExternalApiBundle\Command\UpdateDebitCardBinlistCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateDebitCardBinlistCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldUpdateBankIfBankNameHasChanged()
    {
        $this->load(true);
        $em = $this->getEntityManager();

        $bank = new BinlistBank();
        $bank->setBankName('AMERICAN EXPRESS');
        $debitCardBinlist = new DebitCardBinlist();
        $debitCardBinlist->setIin(341142);
        $debitCardBinlist->setBinlistBank($bank);
        $debitCardBinlist->setCardType('DEBIT');
        $em->persist($debitCardBinlist);
        $em->flush();

        $this->executeCommand();

        /** @var DebitCardBinlist $debitCard */
        $debitCard = $em->getRepository('RjDataBundle:DebitCardBinlist')->findOneBy(['iin' => 341142]);
        $this->assertNotNull($debitCard, 'DebitCard was not saved');
        $this->assertNotNull($debitCardBank = $debitCard->getBinlistBank(), 'Debit Card should have a bank');
        $this->assertNotEquals('AMERICAN EXPRESS', $debitCardBank->getBankName(), 'DebitCard should have bank ABC');
    }

    /**
     * @test
     */
    public function shouldUpdateFieldsOnlyIfTheyHaveChanged()
    {
        $this->load(true);
        $em = $this->getEntityManager();

        $bank = new BinlistBank();
        $bank->setBankName('ABC America');
        $debitCardBinlist = new DebitCardBinlist();
        $debitCardBinlist->setIin(341143);
        $debitCardBinlist->setBinlistBank($bank);
        $debitCardBinlist->setBankPhone('12345689');
        $debitCardBinlist->setCardBrand('DINERS CLUB');
        $debitCardBinlist->setCardType('CREDIT');
        $debitCardBinlist->setBankUrl('www.abc-a.com');
        $debitCardBinlist->setBankCity('NY');
        $em->persist($debitCardBinlist);
        $em->flush();

        $this->executeCommand();

        /** @var DebitCardBinlist $debitCard */
        $debitCard = $em->getRepository('RjDataBundle:DebitCardBinlist')->findOneBy(['iin' => 341143]);
        $this->assertEquals($bank, $debitCardBinlist->getBinlistBank(), 'Bank should not be changed');
        $this->assertNotEquals('123456789', $debitCardBinlist->getBankPhone(), 'Bank phone should be changed');
        $this->assertEquals('www.abc-a.com', $debitCardBinlist->getBankUrl(), 'Bank url should not be changed');
        $this->assertNotEquals('NY', $debitCardBinlist->getBankCity(), 'Bank city should be changed');
        $this->assertNotEquals('DINERS CLUB', $debitCardBinlist->getCardBrand(), 'Card brand should be changed');
        $this->assertEquals('CREDIT', $debitCardBinlist->getCardType(), 'Card type should not be changed');
    }

    protected function executeCommand()
    {
        $application = new Application($this->getKernel());
        $binlistCommand = new UpdateDebitCardBinlistCommand();
        $binlistCommand->setContainer($this->getContainerMock());
        $application->add($binlistCommand);

        $command = $application->find('api:binlist:update-data');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }

    protected function getContainerMock()
    {
        $binlistSourceMock = $this->getMock(
            '\RentJeeves\ExternalApiBundle\Services\Binlist',
            ['loadBinlistData'],
            [],
            '',
            false
        );
        $binlistSourceMock
            ->expects($this->once())
            ->method('loadBinlistData')
            ->will($this->returnValue(
                [
                    0 => [
                        'iin' => 341142,
                        'bank_name' => 'ABC',
                        'card_type' => 'DEBIT',
                    ],
                    1 => [
                        'iin' => 341143,
                        'bank_name' => 'ABC America',
                        'card_brand' => 'AMEX',
                        'card_type' => 'CREDIT',
                        'bank_phone' => '05989659999',
                        'bank_url' => 'www.abc-a.com',
                        'bank_city' => 'New York City'
                    ]
                ]
            ));

        $this->getContainer()->set('binlist.source', $binlistSourceMock);

        return $this->getContainer();
    }
}
