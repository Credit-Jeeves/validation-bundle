<?php

namespace RentJeeves\DataBundle\Tests\Unit;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\TestBundle\BaseTestCase;
use DateTime;

class UnitCase extends BaseTestCase
{
    /**
     * @test
     */
    public function makeSureContractWaitingIsRemoved()
    {
        $this->load(true);
        $doctrine = $this->getContainer()->get('doctrine');
        /**
         * @var $em EntityManager
         */
        $em = $doctrine->getManager();
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name' => '1-a'
            )
        );
        $this->assertNotNull($unit);
        $this->assertTrue($unit->getContractsWaiting()->count() === 0);

        $contractWaiting = new ContractWaiting();
        $contractWaiting->setUnit($unit);
        $contractWaiting->setResidentId('test');
        $contractWaiting->setImportedBalance('3333');
        $contractWaiting->setFinishAt(new DateTime());
        $contractWaiting->setStartAt(new DateTime());
        $contractWaiting->setRent('7777');
        $contractWaiting->setFirstName('Hi');
        $contractWaiting->setLastName('ho');

        $em->persist($contractWaiting);
        $em->flush();
        $id = $contractWaiting->getId();
        $em->clear();
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name' => '1-a'
            )
        );
        $this->assertNotNull($unit);
        $this->assertTrue($unit->getContractsWaiting()->count() === 1);
        $em->remove($unit);
        $em->flush();
        $em->clear();

        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->find($id);
        $this->assertEmpty($contractWaiting);
    }
}
