<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Forms\Enum\ReportingType;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\Contract;

class ContractsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Contract';

    public static function getContractDataProvider()
    {
        return [
            [ 1, 'json', 200, true ],
            [ 2, 'json', 200, true ],
            [ 3, 'json', 200, false],
        ];
    }

    /**
     * @test
     * @dataProvider getContractDataProvider
     */
    public function getContract($id, $format, $statusCode, $checkBalance)
    {
        $client = $this->getClient();

        $encodedId = $this->getIdEncoder()->encode($id);

        $client->request(
            'GET',
            self::URL_PREFIX . "/contracts/{$encodedId}.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getTenant();

        /** @var Contract $result */
        $result = $repo->findOneBy(['tenant' => $tenant, 'id' => $id]);

        $this->assertNotNull($result);

        $this->assertEquals(
            $result->getId(),
            $this->getIdEncoder()->decode($answer['id'])
        );

        $this->assertEquals(
            $result->getId(),
            $this->getUrlEncoder()->decode($answer['url'])
        );

        $this->assertEquals(
            $result->getUnit()->getId(),
            $this->getUrlEncoder()->decode($answer['unit_url'])
        );

        $this->assertEquals(
            $result->getStatus(),
            $answer['status']
        );

        $this->assertEquals(
            number_format($result->getRent(), 2, '.', ''),
            $answer['rent']
        );

        $leaseStartResult = $result->getStartAt() ? $result->getStartAt()->format('Y-m-d') : '';

        $this->assertEquals(
            $leaseStartResult,
            $answer['lease_start']
        );

        $leaseEndResult = $result->getFinishAt() ? $result->getFinishAt()->format('Y-m-d') : '';

        $this->assertEquals(
            $leaseEndResult,
            $answer['lease_end']
        );

        $dueDateResult = $result->getDueDate() ?  $result->getDueDate() : '';

        $this->assertEquals(
            $dueDateResult,
            $answer['due_date']
        );

        $this->assertEquals(
            $result->getReportToExperian(),
            ReportingType::getMapValue($answer['experian_reporting'])
        );

        if ($checkBalance) {
            $this->assertEquals(
                number_format($result->getIntegratedBalance(), 2, '.', ''),
                $answer['balance']
            );
        } else {
            $this->assertTrue(!isset($answer['balance']));
        }
    }

    public static function contractsDataProvider()
    {
        return [
            [
                'unit_url' => 'unit_url/656765400',
                'experian_reporting' => 'enabled',
            ],
            [
                'new_unit' => [
                    'address' => [
                        'street' => '320 North Dearborn Street',
                        'city' => 'Chicago',
                        'state' => 'IL',
                        'zip' => '60654',
                    ],
                    'landlord' => [
                        'first_name' => 'Test',
                        'last_name' => 'Name',
                        'email' => 'test_landlord1@gmail.com',
                        'phone' => '999-555-55-55',
                    ],
                ],
            ],
            [
                'new_unit' => [
                    'address' => [
                        'street' => '770 Broadway',
                        'unit_name' => '3-a',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip' => '10003',
                    ],
                    'landlord' => [
                        'email' => 'test_landlord2@gmail.com',
                    ],
                ],
                'experian_reporting' => 'enabled',
            ],
            [
                'unit_url' => 'unit_url/2974582658',
            ],
            [
                'unit_url' => 'unit_url/2511139177', // 0
            ],
            [
                'new_unit' => [
                    'address' => [
                        'street' => '22Broadway',
                    ],
                    'landlord' => [
                        'email' => 'test_landlord3gmail.com',
                    ],
                ],
            ],
            [
                'experian_reporting' => 'enabled',
            ],
            [
                'new_unit' => [
                    'address' => [
                        'street' => '44 Test Street',
                        'city' => 'Test',
                        'state' => 'NY',
                        'zip' => '222222',
                    ],
                    'landlord' => [
                        'email' => 'test_landlord3@gmail.com',
                    ],
                ],
            ],
            [
                'new_unit' => [
                    'address' => [
                        'street' => '770 Broadway',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip' => '10003',
                    ],
                    'landlord' => [
                        'email' => 'test_landlord4@gmail.com',
                    ],
                ],
                'experian_reporting' => 'enabled',
            ],
        ];
    }

    public static function createContractDataProvider()
    {
        return [
            [
                'json',
                201,
                self::contractsDataProvider()[0],
            ],
            [
                'json',
                201,
                self::contractsDataProvider()[1]
            ],
            [
                'json',
                201,
                self::contractsDataProvider()[2]
            ],
            [
                'json',
                201,
                self::contractsDataProvider()[3]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createContractDataProvider
     */
    public function createContract($format, $statusCode, $requestParams)
    {
        $client = $this->getClient();

        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'POST',
            self::URL_PREFIX . "/contracts.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ],
            $serializer->serialize($requestParams, $format)
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);

        $tenant = $this->getTenant();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $this->assertNotNull(
            $repo->findOneBy([
                'tenant' => $tenant,
                'id' => $this->getIdEncoder()->decode($answer['id'])
            ])
        );
    }

    public static function editContactDataProvider()
    {
        return [
            [
                'json',
                204,
                [
                    'experian_reporting' => 'enabled',
                ]
            ],
            [
                'json',
                204,
                [
                    'experian_reporting' => 'disabled',
                ]
            ]
        ];
    }

    /**
     * @test
     * @depends createContract
     * @dataProvider editContactDataProvider
     */
    public function editContract($format, $statusCode, $requestParams)
    {
        $client = $this->getClient();

        $tenant = $this->getTenant();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $last = $repo->findOneBy([
            'tenant' => $tenant,
        ], ['id' => 'DESC']);

        $encodedId = $this->getIdEncoder()->encode($last->getId());
        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'PUT',
            self::URL_PREFIX . "/contracts/{$encodedId}.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ],
            $serializer->serialize($requestParams, $format)
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        /** @var Contract $contract */
        $contract = $repo->findOneBy([
            'tenant' => $tenant, 'id' => $last->getId()
        ], ['id' => 'DESC']);

        $this->assertEquals(!!$contract->getReportToExperian(), ReportingType::getMapValue($requestParams['experian_reporting']));
    }

    public static function wrongContractDataProvider()
    {
        return [
            [
                'json',
                400,
                self::contractsDataProvider()[4],
                [
                    [
                        'parameter' => 'unit_url',
                        'message' => 'This value is not valid.'
                    ],
                ]
            ],
            [
                'json',
                400,
                self::contractsDataProvider()[5],
                [
                    [
                        'parameter' => 'new_unit_address_number',
                        'message' => 'api.errors.property.number.empty'
                    ],
                    [
                        'parameter' => 'new_unit_address_state',
                        'message' => 'api.errors.property.state.empty'
                    ],
                    [
                        'parameter' => 'new_unit_address_city',
                        'message' => 'api.errors.property.city.empty'
                    ],
                    [
                        'parameter' => 'new_unit_address_zip',
                        'message' => 'api.errors.property.zip.empty'
                    ],
                    [
                        'parameter' => 'new_unit_landlord_email',
                        'message' => 'This value is not a valid email address.',
                        'value' => 'test_landlord3gmail.com'
                    ],
                ]
            ],
            [
                'json',
                400,
                self::contractsDataProvider()[6],
                [
                    [
                        'parameter' => 'new_unit_address_street',
                        'message' => 'api.errors.property.street.empty'
                    ],
                    [
                        'parameter' => 'new_unit_address_number',
                        'message' => 'api.errors.property.number.empty'
                    ],
                    [
                        'parameter' => 'new_unit_address_state',
                        'message' => 'api.errors.property.state.empty'
                    ],
                    [
                        'parameter' => 'new_unit_address_city',
                        'message' => 'api.errors.property.city.empty'
                    ],
                    [
                        'parameter' => 'new_unit_address_zip',
                        'message' => 'api.errors.property.zip.empty'
                    ],
                    [
                        'parameter' => 'new_unit_landlord_email',
                        'message' => 'email.required',
                    ],
                ]
            ],
            [
                'json',
                400,
                self::contractsDataProvider()[7],
                [
                    [
                        'message' => 'api.errors.contracts.property.invalid'
                    ],
                ]
            ],
            [
                'json',
                400,
                self::contractsDataProvider()[8],
                [
                    [
                        'message' => 'api.errors.contracts.property.not_standalone'
                    ],
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider wrongContractDataProvider
     */
    public function wrongCreateContract($format, $statusCode, $requestParams, $result)
    {
        $client = $this->getClient();

        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'POST',
            self::URL_PREFIX . "/contracts.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ],
            $serializer->serialize($requestParams, $format)
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $this->assertResponseContent($client->getResponse()->getContent(), $result, $format);
    }
}
