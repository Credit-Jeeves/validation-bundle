<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Forms\Enum\ReportingType;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;

class ContractsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Contract';

    const REQUEST_URL = 'contracts';

    public static function getContractDataProvider()
    {
        return [
            [ 1, true ],
            [ 2, true ],
            [ 3, false],
        ];
    }

    /**
     * @test
     * @dataProvider getContractDataProvider
     */
    public function getContract($id, $checkBalance, $format = 'json', $statusCode = 200)
    {
        $encodedId = $this->getIdEncoder()->encode($id);

        $response = $this->getRequest($encodedId, [], $format);

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);

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
                        'unit_name' => '',
                        'street' => '320 North Dearborn Street',
                        'city' => 'Chicago',
                        'state' => 'IL',
                        'zip' => '60654',
                    ],
                    'landlord' => [
                        'first_name' => 'Test',
                        'last_name' => 'Name',
                        'email' => 'test_landlord1@gmail.com',
                        'phone' => '999-555-5555',
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
                        'phone' => '111-111-111'
                    ],
                ],
            ],
            [
                'experian_reporting' => 'enabled',
            ],
            [
                'new_unit' => [
                    'address' => [
                        'unit_name' => '',
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
                        'unit_name' => '',
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
            [
                'unit_url' => 'unit_url/2511139177', // 0
                'new_unit' => [
                    'address' => [
                        'unit_name' => '',
                        'street' => '770 Broadway',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip' => '10003',
                    ],
                    'landlord' => [
                        'email' => 'test_landlord4@gmail.com',
                    ],
                ],
            ],
            [
                'experian_reporting' => 'enable',
            ],
        ];
    }

    public static function createContractDataProvider()
    {
        return [
            [
                self::contractsDataProvider()[0],
            ],
            [
                self::contractsDataProvider()[1]
            ],
            [
                self::contractsDataProvider()[2]
            ],
            [
                self::contractsDataProvider()[3]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createContractDataProvider
     */
    public function createContract($requestParams, $format = 'json', $statusCode = 201)
    {
        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);

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
                [
                    'experian_reporting' => 'enabled',
                ]
            ],
            [
                [
                    'experian_reporting' => 'disabled',
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider editContactDataProvider
     */
    public function editContract($requestParams, $format = 'json', $statusCode = 204)
    {
        $tenant = $this->getTenant();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $last = $repo->findOneBy([
            'tenant' => $tenant,
        ], ['id' => 'DESC']);

        $encodedId = $this->getIdEncoder()->encode($last->getId());

        $response = $this->putRequest($encodedId, $requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $this->getEm()->refresh($last);

        $this->assertEquals(
            !!$last->getReportToExperian(),
            ReportingType::getMapValue($requestParams['experian_reporting'])
        );
    }

    public static function wrongContractDataProvider()
    {
        return [
            [
                self::contractsDataProvider()[4],
                [
                    [
                        'parameter' => 'unit_url',
                        'message' => 'This value is not valid.'
                    ],
                ]
            ],
            [
                self::contractsDataProvider()[5],
                [
                    [
                        'parameter' => 'new_unit_address_unit_name',
                        'message' => 'api.errors.property.unit_name.specify'
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
                        'message' => 'This value is not a valid email address.',
                        'value' => 'test_landlord3gmail.com'
                    ],
                    [
                        'parameter' => 'new_unit_landlord_phone',
                        'message' => 'error.user.phone.format',
                        'value' => '111111111'
                    ],
                ]
            ],
            [
                self::contractsDataProvider()[6],
                [
                    [
                        'parameter' => 'new_unit_address_unit_name',
                        'message' => 'api.errors.property.unit_name.specify'
                    ],
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
                self::contractsDataProvider()[7],
                [
                    [
                        'message' => 'api.errors.contracts.property.invalid'
                    ],
                ]
            ],
            [
                self::contractsDataProvider()[8],
                [
                    [
                        'message' => 'api.errors.contracts.property.not_standalone'
                    ],
                ]
            ],
            [
                self::contractsDataProvider()[9],
                [
                    [
                        'message' => 'api.errors.contract.new_unit.unit_url.collision',
                        'value' => [],
                        'parameter' => '_globals',
                    ],
                    [
                        'parameter' => 'unit_url',
                        'message' => 'This value is not valid.',
                    ],
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider wrongContractDataProvider
     */
    public function wrongCreateContract($requestParams, $result, $format = 'json', $statusCode = 400)
    {
        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $this->assertResponseContent($response->getContent(), $result, $format);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function wrongEnabledCreate()
    {
        $requestParams = [
            'unit_url' => 'unit_url/2974582658',
            'experian_reporting' => 'enable',
        ];

        $this->createContract($requestParams, 'json', '400');
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function wrongEnabledEdit()
    {
        $requestParams = [
            'experian_reporting' => 'disable',
        ];

        $this->editContract($requestParams, 'json', '400');
    }

    public static function setExperianReportingStartAtDataProvider()
    {
        return [
            [
                [
                    'unit_url' => 'unit_url/2974582658',
                    'experian_reporting' => 'enabled'
                ],
                true,
                'now'
            ],
            [
                [
                    'unit_url' => 'unit_url/2974582658',
                ],
                false,
            ],
            [
                [
                    'unit_url' => 'unit_url/2974582658',
                    'experian_reporting' => 'disabled'
                ],
                false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider setExperianReportingStartAtDataProvider
     */
    public function setExperianReportingStartAt($requestParameters, $reportingStatus, $reportingStartAt = null)
    {
        $reportingStartAt = $reportingStartAt ? (new DateTime($reportingStartAt))->format('Y-m-d'): null;

        $this->createContract($requestParameters);

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $tenant = $this->getTenant();
        /** @var Contract $last */
        $last = $repo->findOneBy([
            'tenant' => $tenant,
        ], ['id' => 'DESC']);

        $this->assertEquals($reportingStatus, $last->getReportToExperian());

        $startAt = $last->getExperianStartAt();
        !$startAt || $startAt = $last->getExperianStartAt()->format('Y-m-d');

        $this->assertEquals($reportingStartAt, $startAt);
    }

    public static function updateExperianReportingStartAtDataProvider()
    {
        return [
            [
                ['experian_reporting' => 'enabled'],
                true,
                'now',
            ],
            [
                ['experian_reporting' => 'disabled'],
                false,
                'now',
            ]
        ];
    }

    /**
     * @test
     * @depends setExperianReportingStartAt
     * @dataProvider updateExperianReportingStartAtDataProvider
     */
    public function updateExperianReportingStartAt($requestParameters, $reportingStatus, $reportingStartAt)
    {
        $this->editContract($requestParameters);

        $reportingStartAt = (new DateTime($reportingStartAt))->format('Y-m-d');

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $tenant = $this->getTenant();
        /** @var Contract $last */
        $last = $repo->findOneBy([
            'tenant' => $tenant,
        ], ['id' => 'DESC']);

        $startAt = $last->getExperianStartAt();
        !$startAt || $startAt = $last->getExperianStartAt()->format('Y-m-d');

        $this->assertEquals($reportingStatus, $last->getReportToExperian());

        $this->assertEquals($reportingStartAt, $startAt);
    }
}
