<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use CreditJeeves\DataBundle\Entity\Address;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;

class AddressesControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'DataBundle:Address';

    const REQUEST_URL = 'addresses';

    /**
     * @return array
     */
    public function getAddressDataProvider()
    {
        return [
            [25],
            [53],
        ];
    }

    /**
     * @param $id
     *
     * @test
     * @dataProvider getAddressDataProvider
     */
    public function getAddress($id)
    {
        $encodedId = $this->getIdEncoder()->encode($id);

        $response = $this->getRequest($encodedId);

        $this->assertResponse($response);

        $answer = $this->parseContent($response->getContent());

        /** @var Address $address */
        $address = $this->getEntityRepository(self::WORK_ENTITY)->find($id);

        $this->assertEquals(
            $address->getId(),
            $this->getIdEncoder()->decode($answer['id'])
        );

        $this->assertEquals(
            $address->getId(),
            $this->getUrlEncoder()->decode($answer['url'])
        );

        $this->assertEquals($address->getAddress(), $answer['street']);

        $this->assertEquals($address->getUnit(), $answer['unit']);

        $this->assertEquals($address->getCity(), $answer['city']);

        $this->assertEquals($address->getArea(), $answer['state']);

        $this->assertEquals($address->getZip(), $answer['zip']);

        $this->assertEquals($address->getIsDefault(), $answer['is_current']);
    }

    /**
     * @test
     */
    public function getAddresses()
    {
        $response = $this->getRequest();

        $this->assertResponse($response);

        $answer = $this->parseContent($response->getContent());

        $this->assertCount($this->getUser()->getAddresses()->count(), $answer);

        // check first and last element
        /** @var Address $address1 */
        $address1 = $this->getUser()->getAddresses()->first();
        /** @var Address $address2 */
        $address2 = $this->getUser()->getAddresses()->last();

        $this->assertEquals(
            $address1->getId(),
            $this->getIdEncoder()->decode($answer[0]['id'])
        );
        $this->assertEquals(
            $address2->getId(),
            $this->getIdEncoder()->decode($answer[count($answer)-1]['id'])
        );

        $this->assertEquals(
            $address1->getId(),
            $this->getUrlEncoder()->decode($answer[0]['url'])
        );
        $this->assertEquals(
            $address2->getId(),
            $this->getUrlEncoder()->decode($answer[count($answer)-1]['url'])
        );

        $this->assertEquals($address1->getAddress(), $answer[0]['street']);
        $this->assertEquals($address2->getAddress(), $answer[count($answer)-1]['street']);

        $this->assertEquals($address1->getUnit(), $answer[0]['unit']);
        $this->assertEquals($address2->getUnit(), $answer[count($answer)-1]['unit']);

        $this->assertEquals($address1->getCity(), $answer[0]['city']);
        $this->assertEquals($address2->getCity(), $answer[count($answer)-1]['city']);

        $this->assertEquals($address1->getArea(), $answer[0]['state']);
        $this->assertEquals($address2->getArea(), $answer[count($answer)-1]['state']);

        $this->assertEquals($address1->getZip(), $answer[0]['zip']);
        $this->assertEquals($address2->getZip(), $answer[count($answer)-1]['zip']);

        $this->assertEquals($address1->getIsDefault(), $answer[0]['is_current']);
        $this->assertEquals($address2->getIsDefault(), $answer[count($answer)-1]['is_current']);
    }

    /**
     * @return array
     */
    public static function createAddressDataProvider()
    {
        return [
            [
                [
                    'street' => '111 KINGSTON DR',
                    'unit' => '1-c',
                    'city' => 'LAWRENCE',
                    'state' => 'KS',
                    'zip' => '660414',
                    'is_current' => true,
                ]
            ],
            [
                [
                    'street' => '111 KINGSTON DR',
                    'unit' => '1-d',
                    'city' => 'LAWRENCE',
                    'state' => 'KS',
                    'zip' => '660414',
                ]
            ],
        ];
    }

    /**
     * @param array $requestParams
     * @param int   $statusCode
     *
     * @test
     * @dataProvider createAddressDataProvider
     */
    public function createAddress($requestParams, $statusCode = 201)
    {
        $response = $this->postRequest($requestParams);

        $this->assertResponse($response, $statusCode);

        $answer = $this->parseContent($response->getContent());

        $tenant = $this->getUser();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        /** @var Address $address */
        $address = $repo->findOneBy([
            'user' => $tenant,
            'id' => $this->getIdEncoder()->decode($answer['id'])
        ]);

        $this->assertNotNull($address);

        $this->assertEquals($requestParams['street'], $address->getAddress());

        $this->assertEquals($requestParams['unit'], $address->getUnit());

        $this->assertEquals($requestParams['city'], $address->getCity());

        $this->assertEquals($requestParams['zip'], $address->getZip());

        $this->assertEquals(
            isset($requestParams['is_current']) ? $requestParams['is_current'] : false,
            $address->getIsDefault()
        );
    }

    /**
     * @test
     */
    public function editFullAddress()
    {
        $requestParams = [
            'street' => '113 Kingston Dr',
            'unit' => '2210',
            'city' => 'Lawrence',
            'state' => 'KY',
            'zip' => '40150',
            'is_current' => true,
        ];

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        /** @var Address $address */
        $address = $repo->findOneBy(['user' => $this->getUser()], ['id' => 'ASC']);

        $encodedId = $this->getIdEncoder()->encode($address->getId());

        $response = $this->putRequest($encodedId, $requestParams);

        $this->getEm()->refresh($address);

        $this->assertResponse($response, 204);

        $this->assertEquals($requestParams['street'], $address->getAddress());

        $this->assertEquals($requestParams['unit'], $address->getUnit());

        $this->assertEquals($requestParams['city'], $address->getCity());

        $this->assertEquals($requestParams['zip'], $address->getZip());

        $this->assertEquals($requestParams['is_current'], $address->getIsDefault());
    }

    /**
     * @test
     */
    public function shouldBeOnlyOneDefaultAddress()
    {
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        /** @var Address[] $result */
        $result = $repo->findBy(['user' => $this->getUser(), 'isDefault' => true]);

        $this->assertCount(1, $result);

        $encodedId = $this->getIdEncoder()->encode($result[0]->getId());

        $response = $this->putRequest($encodedId, $this->mapRequestParameters($result[0], false));

        $this->assertResponse($response, 204);

        /** @var Address[] $result */
        $result = $repo->findBy(['user' => $this->getUser(), 'isDefault' => true]);

        $this->assertCount(0, $result);

        /** @var Address[] $noDefaultAddresses */
        $noDefaultAddresses = $repo->findBy(['user' => $this->getUser(), 'isDefault' => false]);

        // try update 2 different address and set it like default and check that we have only one default address
        $this->assertGreaterThan(2, count($noDefaultAddresses));

        $encodedId = $this->getIdEncoder()->encode($noDefaultAddresses[0]->getId());

        $response = $this->putRequest(
            $encodedId,
            $this->mapRequestParameters($noDefaultAddresses[0], true)
        );

        $this->assertResponse($response, 204);
        /** @var Address[] $result */
        $result = $repo->findBy(['user' => $this->getUser(), 'isDefault' => true]);

        $this->assertCount(1, $result);

        $this->assertEquals($noDefaultAddresses[0]->getId(), $result[0]->getId());

        $encodedId = $this->getIdEncoder()->encode($noDefaultAddresses[count($noDefaultAddresses)-1]->getId());

        $response = $this->putRequest(
            $encodedId,
            $this->mapRequestParameters($noDefaultAddresses[count($noDefaultAddresses)-1], true)
        );

        $this->assertResponse($response, 204);
        /** @var Address[] $result */
        $result = $repo->findBy(['user' => $this->getUser(), 'isDefault' => true]);

        $this->assertCount(1, $result);

        $this->assertEquals($noDefaultAddresses[count($noDefaultAddresses)-1]->getId(), $result[0]->getId());
    }

    /**
     * @param Address $address
     * @param bool $isDefault
     * @return array
     */
    protected function mapRequestParameters(Address $address, $isDefault)
    {
        return [
            'street' => $address->getAddress(),
            'unit' => $address->getUnit(),
            'city' => $address->getCity(),
            'state' => $address->getArea(),
            'zip' => $address->getZip(),
            'is_current' => $isDefault,
        ];
    }
}
