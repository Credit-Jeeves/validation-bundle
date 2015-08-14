<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use RentJeeves\ApiBundle\Forms\Enum\ReportingType;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ApiBundle\Response\Contract as ContractResponseEntity;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;

class ContractsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Contract';

    const REQUEST_URL = 'contracts';

    /**
     * @return array
     */
    public static function getContractDataProvider()
    {
        return [
            [ 1, true ],
            [ 2, true ],
            [ 3, false],
        ];
    }

    /**
     * @param int  $id
     * @param bool $checkBalance
     *
     * @test
     * @dataProvider getContractDataProvider
     */
    public function getContract($id, $checkBalance)
    {
        $encodedId = $this->getIdEncoder()->encode($id);

        $response = $this->getRequest($encodedId);

        $this->assertResponse($response);

        $answerFromApi = $this->parseContent($response->getContent());

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getUser();

        /** @var Contract $contractInDB */
        $contractInDB = $repo->findOneBy(['tenant' => $tenant, 'id' => $id]);

        $this->assertNotNull($contractInDB);

        $this->assertEquals(
            $contractInDB->getId(),
            $this->getIdEncoder()->decode($answerFromApi['id'])
        );

        $this->assertEquals(
            $contractInDB->getId(),
            $this->getUrlEncoder()->decode($answerFromApi['url'])
        );

        $this->assertEquals(
            $contractInDB->getUnit()->getId(),
            $this->getUrlEncoder()->decode($answerFromApi['unit_url'])
        );

        $this->assertEquals(
            $contractInDB->getStatus(),
            $answerFromApi['status']
        );

        $this->assertEquals(
            number_format($contractInDB->getRent(), 2, '.', ''),
            $answerFromApi['rent']
        );

        $leaseStartResult = $contractInDB->getStartAt() ? $contractInDB->getStartAt()->format('Y-m-d') : '';

        $this->assertEquals(
            $leaseStartResult,
            $answerFromApi['lease_start']
        );

        $this->assertEquals(
            ContractResponseEntity::DELIVERY_METHOD_ELECTRONIC,
            $answerFromApi['delivery_method']
        );

        $this->assertArrayHasKey(
            'mailing_address',
            $answerFromApi
        );

        $mailingAddress = $answerFromApi['mailing_address'];
        $this->assertEquals(
            $contractInDB->getGroup()->getMailingAddressName(),
            $mailingAddress['name']
        );
        $this->assertEquals(
            $contractInDB->getGroup()->getStreetAddress1(),
            $mailingAddress['street_address_1']
        );

        $this->assertEquals(
            $contractInDB->getGroup()->getStreetAddress2(),
            $mailingAddress['street_address_2']
        );

        $this->assertEquals(
            $contractInDB->getGroup()->getCity(),
            $mailingAddress['city']
        );

        $this->assertEquals(
            $contractInDB->getGroup()->getState(),
            $mailingAddress['state']
        );

        $this->assertEquals(
            $contractInDB->getGroup()->getZip(),
            $mailingAddress['zip']
        );

        $leaseEndResult = $contractInDB->getFinishAt() ? $contractInDB->getFinishAt()->format('Y-m-d') : '';

        $this->assertEquals(
            $leaseEndResult,
            $answerFromApi['lease_end']
        );

        $dueDateResult = $contractInDB->getDueDate() ?  $contractInDB->getDueDate() : '';

        $this->assertEquals(
            $dueDateResult,
            $answerFromApi['due_date']
        );

        $this->assertEquals(
            $contractInDB->getReportToExperian(),
            ReportingType::getMapValue($answerFromApi['experian_reporting'])
        );

        if ($checkBalance) {
            $this->assertEquals(
                number_format($contractInDB->getIntegratedBalance(), 2, '.', ''),
                $answerFromApi['balance']
            );
        } else {
            $this->assertArrayNotHasKey('balance', $answerFromApi);
        }
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
     * @param array $requestParams
     * @param int   $statusCode
     *
     * @test
     * @dataProvider createContractDataProvider
     */
    public function createContract($requestParams, $statusCode = 201)
    {
        $response = $this->postRequest($requestParams);

        $this->assertResponse($response, $statusCode);

        $answer = $this->parseContent($response->getContent());

        $tenant = $this->getUser();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $this->assertNotNull(
            $repo->findOneBy([
                'tenant' => $tenant,
                'id' => $this->getIdEncoder()->decode($answer['id'])
            ])
        );
    }

    /**
     * @return array
     */
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
     * @param array $requestParams
     * @param int   $statusCode
     *
     * @test
     * @dataProvider editContactDataProvider
     */
    public function editContract($requestParams, $statusCode = 204)
    {
        $tenant = $this->getUser();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $last = $repo->findOneBy([
            'tenant' => $tenant,
        ], ['id' => 'DESC']);

        $encodedId = $this->getIdEncoder()->encode($last->getId());

        $response = $this->putRequest($encodedId, $requestParams);

        $this->assertResponse($response, $statusCode);

        $this->getEm()->refresh($last);

        $this->assertEquals(
            !!$last->getReportToExperian(),
            ReportingType::getMapValue($requestParams['experian_reporting'])
        );
    }

    /**
     * @return array
     */
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
     * @param array $requestParams
     * @param array $result
     * @param int   $statusCode
     *
     * @test
     * @dataProvider wrongContractDataProvider
     */
    public function wrongCreateContract($requestParams, $result, $statusCode = 400)
    {
        $response = $this->postRequest($requestParams);

        $this->assertResponse($response, $statusCode);

        $this->assertResponseContent($response->getContent(), $result);
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

        $this->createContract($requestParams, '400');
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

        $this->editContract($requestParams, '400');
    }

    /**
     * @return array
     */
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
     * @param array       $requestParameters
     * @param bool        $reportingStatus
     * @param string|null $reportingStartAt
     *
     * @test
     * @dataProvider setExperianReportingStartAtDataProvider
     */
    public function setExperianReportingStartAt($requestParameters, $reportingStatus, $reportingStartAt = null)
    {
        $reportingStartAt = $reportingStartAt ? (new DateTime($reportingStartAt))->format('Y-m-d') : null;

        $this->createContract($requestParameters);

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $tenant = $this->getUser();
        /** @var Contract $last */
        $last = $repo->findOneBy([
            'tenant' => $tenant,
        ], ['id' => 'DESC']);

        $this->assertEquals($reportingStatus, $last->getReportToExperian());

        $startAt = $last->getExperianStartAt();
        !$startAt || $startAt = $last->getExperianStartAt()->format('Y-m-d');

        $this->assertEquals($reportingStartAt, $startAt);
    }

    /**
     * @return array
     */
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
     * @param array  $requestParameters
     * @param bool   $reportingStatus
     * @param string $reportingStartAt
     *
     * @test
     * @depends setExperianReportingStartAt
     * @dataProvider updateExperianReportingStartAtDataProvider
     */
    public function updateExperianReportingStartAt($requestParameters, $reportingStatus, $reportingStartAt)
    {
        $this->editContract($requestParameters);

        $reportingStartAt = (new DateTime($reportingStartAt))->format('Y-m-d');

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $tenant = $this->getUser();
        /** @var Contract $last */
        $last = $repo->findOneBy([
            'tenant' => $tenant,
        ], ['id' => 'DESC']);

        $startAt = $last->getExperianStartAt();
        !$startAt || $startAt = $last->getExperianStartAt()->format('Y-m-d');

        $this->assertEquals($reportingStatus, $last->getReportToExperian());

        $this->assertEquals($reportingStartAt, $startAt);
    }

    /**
     * @test
     */
    public function shouldShowCheckDeliveryMethodIfGroupIsPayDirect()
    {
        $this->load(true);
        $contractId = 1;
        $encodedId = $this->getIdEncoder()->encode($contractId);

        // Send request 1st time - make sure delivery method is electronic
        $response = $this->getRequest($encodedId);
        $this->assertResponse($response);
        $answerFromApi = $this->parseContent($response->getContent());

        $this->assertEquals(
            ContractResponseEntity::DELIVERY_METHOD_ELECTRONIC,
            $answerFromApi['delivery_method']
        );

        /** @var Contract $contractInDB */
        $contractInDB = $this->getEntityRepository(self::WORK_ENTITY)->findOneById($contractId);
        $this->assertNotNull($contractInDB);

        $group = $contractInDB->getGroup();
        $this->assertEquals(OrderAlgorithmType::SUBMERCHANT, $group->getOrderAlgorithm());

        // Set Group to PayDirect order algorithm
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $em = $this->getEntityManager();
        $em->persist($group);
        $em->flush($group);

        // Send request 2nd time - make sure delivery method is check
        $response = $this->getRequest($encodedId);
        $this->assertResponse($response);
        $answerFromApi = $this->parseContent($response->getContent());
        $this->assertEquals(
            ContractResponseEntity::DELIVERY_METHOD_CHECK,
            $answerFromApi['delivery_method']
        );
    }
}
