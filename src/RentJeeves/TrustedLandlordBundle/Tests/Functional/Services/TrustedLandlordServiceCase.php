<?php

namespace RentJeeves\TrustedLandlordBundle\Tests\Functional\Services;

use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\TrustedLandlordType;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO;

class TrustedLandlordServiceCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnNullIfDbDoesntContainAddress()
    {
        $this->load(true);
        $trustedLandlordService = $this->getTrustedLandlordService();

        $trustedLandlordDTO = new TrustedLandlordDTO();
        $trustedLandlordDTO->setType(TrustedLandlordType::PERSON);
        $trustedLandlordDTO->setAddressee('testAddressee');
        $trustedLandlordDTO->setFirstName('testFirstName');
        $trustedLandlordDTO->setLastName('testLastName');
        $trustedLandlordDTO->setPhone('9379992');

        $trustedLandlordDTO->setAddress1('771 Broadway');
        $trustedLandlordDTO->setAddress2('#1');
        $trustedLandlordDTO->setState('NY');
        $trustedLandlordDTO->setCity('New York');
        $trustedLandlordDTO->setZip('10003');

        $result = $trustedLandlordService->lookup($trustedLandlordDTO);
        $this->assertNull($result, 'Service returned bad result.');
    }

    /**
     * @test
     */
    public function shouldReturnTrustedLandlordIfDbContainsAddress()
    {
        $this->load(true);
        $trustedLandlordService = $this->getTrustedLandlordService();

        $trustedLandlordDTO = new TrustedLandlordDTO();
        $trustedLandlordDTO->setType(TrustedLandlordType::PERSON);
        $trustedLandlordDTO->setAddressee('testAddressee');
        $trustedLandlordDTO->setFirstName('testFirstName');
        $trustedLandlordDTO->setLastName('testLastName');
        $trustedLandlordDTO->setPhone('9379992');

        $trustedLandlordDTO->setAddress1('770 Broadway');
        $trustedLandlordDTO->setAddress2('#1');
        $trustedLandlordDTO->setState('NY');
        $trustedLandlordDTO->setCity('New York');
        $trustedLandlordDTO->setZip('10003');

        $result = $trustedLandlordService->lookup($trustedLandlordDTO);
        $this->assertInstanceOf(TrustedLandlord::class, $result);
    }

    /**
     * @test
     * @expectedException \RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordServiceException
     */
    public function shouldThrowExceptionIfDbContainsAddress()
    {
        $trustedLandlordService = $this->getTrustedLandlordService();

        $trustedLandlordDTO = new TrustedLandlordDTO();
        $trustedLandlordDTO->setType(TrustedLandlordType::PERSON);
        $trustedLandlordDTO->setAddressee('testAddressee');
        $trustedLandlordDTO->setFirstName('testFirstName');
        $trustedLandlordDTO->setLastName('testLastName');
        $trustedLandlordDTO->setPhone('9379992');

        $trustedLandlordDTO->setAddress1('770 Broadway');
        $trustedLandlordDTO->setAddress2('#1');
        $trustedLandlordDTO->setState('NY');
        $trustedLandlordDTO->setCity('New York');
        $trustedLandlordDTO->setZip('10003');

        $trustedLandlordService->create($trustedLandlordDTO);
    }

    /**
     * @test
     */
    public function shouldCreateTrustedLandlordAndCheckMailingAddress()
    {
        $this->load(true);
        $allTrustedLandlords = $this->getEntityManager()->getRepository('RjDataBundle:TrustedLandlord')->findAll();
        $countTrustedLandlords = count($allTrustedLandlords);
        $allCheckMailingAddresses = $this->getEntityManager()->getRepository('RjDataBundle:TrustedLandlord')->findAll();
        $countCheckMailingAddresses = count($allCheckMailingAddresses);

        $trustedLandlordService = $this->getTrustedLandlordService();

        $trustedLandlordDTO = new TrustedLandlordDTO();
        $trustedLandlordDTO->setType(TrustedLandlordType::PERSON);
        $trustedLandlordDTO->setAddressee('testAddressee');
        $trustedLandlordDTO->setFirstName('testFirstName');
        $trustedLandlordDTO->setLastName('testLastName');
        $trustedLandlordDTO->setPhone('9379992');

        $trustedLandlordDTO->setAddress1('771 Broadway');
        $trustedLandlordDTO->setAddress2('#1');
        $trustedLandlordDTO->setState('NY');
        $trustedLandlordDTO->setCity('New York');
        $trustedLandlordDTO->setZip('10003');

        $trustedLandlordService->create($trustedLandlordDTO);

        $allTrustedLandlords = $this->getEntityManager()->getRepository('RjDataBundle:TrustedLandlord')->findAll();
        $countTrustedLandlordsAfterCreate = count($allTrustedLandlords);
        $allCheckMailingAddresses = $this->getEntityManager()->getRepository('RjDataBundle:TrustedLandlord')->findAll();
        $countCheckMailingAddressesAfterCreate = count($allCheckMailingAddresses);

        $this->assertEquals(
            $countTrustedLandlordsAfterCreate,
            $countTrustedLandlords + 1,
            'TrustedLandlord is not created.'
        );
        $this->assertEquals(
            $countCheckMailingAddressesAfterCreate,
            $countCheckMailingAddresses + 1,
            'CheckMailingAddress is not created.'
        );

        // TODO: pls add check for status
    }

    /**
     * @return \RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordService
     */
    protected function getTrustedLandlordService()
    {
        return $this->getContainer()->get('trusted_landlord_service');
    }
}
