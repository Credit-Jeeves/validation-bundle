<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\GroupType;
use RentJeeves\ApiBundle\Forms\Enum\ReportingType;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\ApiBundle\Response\Contract as ContractResponseEntity;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\TrustedLandlordType;

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
            [ 1 ],
            [ 2 ],
            [ 3 ],
        ];
    }

    /**
     * @param int  $id
     *
     * @test
     * @dataProvider getContractDataProvider
     *
     * @return array
     */
    public function getContract($id)
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

        $this->assertArrayHasKey('fee_cc', $answerFromApi, 'Api should retrieve fee_cc for contract');
        $this->assertEquals(
            (float) $contractInDB->getGroupSettings()->getFeeCC(),
            $answerFromApi['fee_cc'],
            'Fee CC should be taken from group settings'
        );

        $this->assertArrayHasKey('fee_ach', $answerFromApi, 'Api should retrieve fee_ach for contract');
        $this->assertEquals(
            (float) $contractInDB->getGroupSettings()->getFeeACH(),
            $answerFromApi['fee_ach'],
            'Fee ACH should be taken from group settings'
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

        return [$id, $answerFromApi];
    }

    /**
     * @param array $data
     *
     * @test
     * @depends getContract-1
     */
    public function shouldBePresentCheckingMailingAddressOnTrustedLandlordGroup(array $data)
    {
        list($contractId, $answerFromApi) = $data;
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        /** @var Contract $contractInDB */
        $contractInDB = $repo->find($contractId);
        $this->assertNotNull(
            $contractInDB->getGroup()->getTrustedLandlord(),
            'Group should have trusted landlord'
        );
        $this->assertNotNull(
            $checkingAddressInDB = $contractInDB->getGroup()->getTrustedLandlord()->getCheckMailingAddress(),
            'Group should have checking mailing address'
        );
        $this->assertArrayHasKey(
            'mailing_address',
            $answerFromApi
        );

        $mailingAddress = $answerFromApi['mailing_address'];
        $checkingAddressInDB = $contractInDB->getGroup()->getTrustedLandlord()->getCheckMailingAddress();

        $this->assertEquals(
            $checkingAddressInDB->getAddressee(),
            $mailingAddress['payee_name']
        );
        $this->assertEquals(
            $checkingAddressInDB->getAddress1(),
            $mailingAddress['street_address_1']
        );

        $this->assertEquals(
            $checkingAddressInDB->getAddress2(),
            $mailingAddress['street_address_2']
        );

        $this->assertEquals(
            $checkingAddressInDB->getCity(),
            $mailingAddress['city']
        );

        $this->assertEquals(
            $checkingAddressInDB->getState(),
            $mailingAddress['state']
        );

        $this->assertEquals(
            $checkingAddressInDB->getZip(),
            $mailingAddress['zip']
        );
    }

    /**
     * @param array $data
     *
     * @test
     * @depends getContract-1
     */
    public function shouldBePresentBalanceOnGetContractWhenGroupIsIntegrated(array $data)
    {
        list($contractId, $answerFromApi) = $data;
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        /** @var Contract $contractInDB */
        $contractInDB = $repo->find($contractId);
        $this->assertTrue($contractInDB->getGroupSettings()->getIsIntegrated(), 'Group should be integrated');
        $this->assertArrayHasKey('balance', $answerFromApi, 'Should be present balance on answer from API');
        $this->assertEquals(
            number_format($contractInDB->getIntegratedBalance(), 2, '.', ''),
            $answerFromApi['balance'],
            'Balance should be equals'
        );
    }

    /**
     * @param array $data
     *
     * @test
     * @depends getContract-2
     */
    public function shouldNotBePresentBalanceOnGetContractWhenGroupIsNotIntegrated(array $data)
    {
        list($contractId, $answerFromApi) = $data;
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        /** @var Contract $contractInDB */
        $contractInDB = $repo->find($contractId);
        $this->assertFalse($contractInDB->getGroupSettings()->getIsIntegrated(), 'Group should not be integrated');
        $this->assertArrayNotHasKey('balance', $answerFromApi, 'Should not be present balance on answer from API');
    }

    /**
     * @param array $data
     *
     * @test
     * @depends getContract-1
     */
    public function shouldBePresentLocationIdOnGetContractWhenNotNull(array $data)
    {
        list($contractId, $answerFromApi) = $data;
        $this->assertArrayHasKey(
            'mailing_address',
            $answerFromApi,
            'Answer from api should have mailing_address'
        );
        $mailingAddress = $answerFromApi['mailing_address'];

        $this->assertArrayHasKey('location_id', $mailingAddress, 'Should be present location_id on answer from API');
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        /** @var Contract $contractInDB */
        $contractInDB = $repo->find($contractId);
        $this->assertEquals(
            $contractInDB->getGroup()->getTrustedLandlord()->getCheckMailingAddress()->getExternalLocationId(),
            $mailingAddress['location_id'],
            'Location_id should be equals external group id on DB'
        );
    }

    /**
     * @param array $data
     *
     * @test
     * @depends getContract-2
     */
    public function shouldNotBePresentExternalGroupIdOnGetContractWhenNull(array $data)
    {
        list($contractId, $answerFromApi) = $data;
        $this->assertArrayNotHasKey(
            'location_id',
            $answerFromApi,
            'Should not be present location_id on answer from API'
        );
    }

    /**
     * @test
     */
    public function getContracts()
    {
        $response = $this->getRequest();

        $this->assertResponse($response);

        $answer = $this->parseContent($response->getContent());

        $this->assertInstanceOf('RentJeeves\DataBundle\Entity\Tenant', $this->getUser(), 'User should be "Tenant"');
        $this->assertCount(
            $this->getUser()->getContracts()->count(),
            $answer,
            'Count contracts should be the same from DB and api'
        );

        // check first and last element
        /** @var Contract $contract1 */
        $contract1 = $this->getUser()->getContracts()->first();
        /** @var Contract $contract2 */
        $contract2 = $this->getUser()->getContracts()->last();

        $this->assertArrayHasKey(
            0,
            $answer,
            'Answer from api should be array with numeric keys and has element with key 0'
        );
        $this->assertArrayHasKey(
            count($answer) - 1,
            $answer,
            'Answer from api should be array with numeric keys and has element with key ' . count($answer) - 1
        );
        $firstAnswer = $answer[0];
        $lastAnswer = $answer[count($answer) - 1];

        $shortContractStructure = [
            'id',
            'url',
            'unit_url',
            'status',
            'balance',
            'due_date',
            'rent'
        ];
        $this->assertArrayStructure($firstAnswer, $shortContractStructure);
        $this->assertArrayStructure($lastAnswer, $shortContractStructure);

        $this->assertEquals(
            $contract1->getId(),
            $this->getIdEncoder()->decode($firstAnswer['id']),
            'First contract has incorrect id'
        );
        $this->assertEquals(
            $contract2->getId(),
            $this->getIdEncoder()->decode($lastAnswer['id']),
            'Last contract has incorrect id'
        );

        $this->assertEquals(
            $contract1->getId(),
            $this->getUrlEncoder()->decode($firstAnswer['url']),
            'First contract has incorrect self url'
        );
        $this->assertEquals(
            $contract2->getId(),
            $this->getUrlEncoder()->decode($lastAnswer['url']),
            'Last contract has incorrect self url'
        );

        $this->assertEquals(
            $contract1->getUnit()->getId(),
            $this->getUrlEncoder()->decode($firstAnswer['unit_url']),
            'First contract has incorrect unit_url'
        );
        $this->assertEquals(
            $contract2->getUnit()->getId(),
            $this->getUrlEncoder()->decode($lastAnswer['unit_url']),
            'Last contract has incorrect unit_url'
        );

        $this->assertEquals(
            $contract1->getStatus(),
            $firstAnswer['status'],
            'First contract has incorrect status, should be ' . $contract1->getStatus()
        );
        $this->assertEquals(
            $contract2->getStatus(),
            $lastAnswer['status'],
            'Last contract has incorrect status, should be ' . $contract2->getStatus()
        );

        $this->assertEquals(
            number_format($contract1->getRent(), 2, '.', ''),
            $firstAnswer['rent'],
            'First contract has incorrect rent, should be ' . number_format($contract1->getRent(), 2, '.', '')
        );
        $this->assertEquals(
            number_format($contract2->getRent(), 2, '.', ''),
            $lastAnswer['rent'],
            'Last contract has incorrect rent, should be ' . number_format($contract2->getRent(), 2, '.', '')
        );

        $this->assertEquals(
            $contract1->getDueDate(),
            $firstAnswer['due_date'],
            'First contract has incorrect due date, should be ' . $contract1->getDueDate()
        );
        $this->assertEquals(
            $contract2->getDueDate(),
            $lastAnswer['due_date'],
            'Last contract has incorrect due date, should be ' . $contract2->getDueDate()
        );

        $this->assertEquals(
            number_format($contract1->getIntegratedBalance(), 2, '.', ''),
            $firstAnswer['balance'],
            'First contract has incorrect balance, should be ' .
            number_format($contract1->getIntegratedBalance(), 2, '.', '')
        );
        $this->assertEquals(
            number_format($contract2->getIntegratedBalance(), 2, '.', ''),
            $lastAnswer['balance'],
            'Last contract has incorrect balance, should be ' .
            number_format($contract2->getIntegratedBalance(), 2, '.', '')
        );
    }

    /**
     * @return array
     */
    public static function contractsDataProvider()
    {
        return [
            // 0
            [
                'unit_url' => 'unit_url/656765400',
                'rent' => 500,
                'due_date' => 10,
                'lease_start' => '2015-01-01',
                'lease_end' => '2020-01-01',
                'experian_reporting' => 'enabled',
            ],
            // 1
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
                        'type' => 'person',
                        'first_name' => 'Test',
                        'last_name' => 'Name',
                        'email' => 'test_landlord1@gmail.com',
                        'phone' => '999-555-5555',
                        'mailing_address' => [
                            'payee_name' => 'Test Name',
                            'street_address_1' => '770 Broadway',
                            'city' => 'New York',
                            'state' => 'NY',
                            'zip' => '10003'
                        ],
                    ],
                ],
                'rent' => 700,
                'due_date' => 1,
                'lease_start' => '2015-02-02',
                'lease_end' => '2020-02-02',
            ],
            // 2
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
                        'type' => 'company',
                        'company_name' => 'Test Company Name',
                        'email' => 'test_landlord1@gmail.com',
                        'mailing_address' => [
                            'payee_name' => 'Test Name',
                            'street_address_1' => '770 Broadway',
                            'city' => 'New York',
                            'state' => 'NY',
                            'zip' => '10003'
                        ],
                    ],
                ],
                'experian_reporting' => 'enabled',
                'rent' => 600,
                'due_date' => 5,
                'lease_start' => '2015-03-03',
                'lease_end' => '2020-03-03',
            ],
            // 3
            [
                'unit_url' => 'unit_url/2974582658',
                'rent' => 800,
                'due_date' => 3,
                'lease_start' => '2015-01-01',
                'lease_end' => '2020-03-03',
            ],
            // 4
            [
                'unit_url' => 'unit_url/2511139177',
                'rent' => 800,
                'due_date' => 3,
                'lease_start' => '2015-01-01',
                'lease_end' => '2020-03-03',
            ],
            // 5
            [
                'new_unit' => [
                    'address' => [
                        'street' => '22Broadway',
                    ],
                    'landlord' => [
                        'type' => 'campany',
                        'email' => 'test_landlord3gmail.com',
                        'phone' => '111-111-111'
                    ],
                ],
                'rent' => 800,
                'due_date' => 3,
                'lease_start' => '2015-01-01',
                'lease_end' => '2020-03-03',
            ],
            // 6
            [
                'experian_reporting' => 'enabled',
                'rent' => 800,
                'due_date' => 3,
                'lease_start' => '2015-01-01',
                'lease_end' => '2020-03-03',
            ],
            // 7
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
                        'type' => 'person',
                        'first_name' => 'Test',
                        'last_name' => 'Name',
                        'email' => 'test_landlord1@gmail.com',
                        'phone' => '999-555-5555',
                        'mailing_address' => [
                            'payee_name' => 'Test Name',
                            'street_address_1' => '770 Broadway',
                            'city' => 'New York',
                            'state' => 'NY',
                            'zip' => '10003'
                        ],
                    ],
                ],
                'rent' => 800,
                'due_date' => 3,
                'lease_start' => '2015-01-01',
                'lease_end' => '2020-03-03',
            ],
            // 8
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
                        'type' => 'person',
                        'first_name' => 'Test',
                        'last_name' => 'Name',
                        'email' => 'test_landlord1@gmail.com',
                        'phone' => '999-555-5555',
                        'mailing_address' => [
                            'payee_name' => 'Test Name',
                            'street_address_1' => '770 Broadway',
                            'city' => 'New York',
                            'state' => 'NY',
                            'zip' => '10003'
                        ],
                    ],
                ],
                'experian_reporting' => 'enabled',
                'rent' => 800,
                'due_date' => 3,
                'lease_start' => '2015-01-01',
                'lease_end' => '2020-03-03',
            ],
            // 9
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
                'rent' => 800,
                'due_date' => 3,
                'lease_start' => '2015-01-01',
                'lease_end' => '2020-03-03',
            ],
            // 10
            [
                'experian_reporting' => 'enable',
            ],
            // 11
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
                        'type' => 'person',
                        'mailing_address' => [
                            'payee_name' => 'Test Name',
                            'street_address_1' => '770 Broadway',
                            'city' => 'New York',
                            'state' => 'NY',
                            'zip' => '10003'
                        ],
                    ],
                ],
                'experian_reporting' => 'enabled',
                'rent' => 600,
                'due_date' => 5,
                'lease_start' => '2015-03-03',
                'lease_end' => '2020-03-03',
            ],
            // 12
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
                        'type' => 'company',
                        'mailing_address' => [
                            'payee_name' => 'Test Name',
                            'street_address_1' => '770 Broadway',
                            'city' => 'New York',
                            'state' => 'NY',
                            'zip' => '10003'
                        ],
                    ],
                ],
                'experian_reporting' => 'enabled',
                'rent' => 600,
                'due_date' => 5,
                'lease_start' => '2015-03-03',
                'lease_end' => '2020-03-03',
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

        $this->assertArrayHasKey('id', $answer, 'Should have "id" on answer');
        $this->assertArrayHasKey('url', $answer, 'Should have "url" on answer');
        $this->assertArrayHasKey('unit_url', $answer, 'Should have "unit_url" on answer');

        $tenant = $this->getUser();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $this->assertNotNull(
            $contract = $repo->findOneBy(
                [
                    'tenant' => $tenant,
                    'id' => $this->getIdEncoder()->decode($answer['id'])
                ]
            ),
            'Contract was not created'
        );
        $this->assertEquals($requestParams['rent'], $contract->getRent());
        $this->assertEquals($requestParams['due_date'], $contract->getDueDate());
        $this->assertEquals(
            $requestParams['lease_start'],
            $contract->getStartAt()->format('Y-m-d'),
            'Lease start date is wrong'
        );
        $this->assertEquals(
            $requestParams['lease_end'],
            $contract->getFinishAt()->format('Y-m-d'),
            'Lease end date is wrong'
        );
    }

    /**
     * @test
     */
    public function shouldCreateContractWithStatusApprovedIfGroupIsAutoApprove()
    {
        $this->load(true);
        $params = [
            'new_unit' => [
                'address' => [
                    'unit_name' => '',
                    'street' => '320 North Dearborn Street',
                    'city' => 'Chicago',
                    'state' => 'IL',
                    'zip' => '60654',
                ],
                'landlord' => [
                    'type' => 'person',
                    'first_name' => 'John',
                    'last_name' => 'Brown',
                    'email' => 'test_landlord4@gmail.com',
                    'phone' => '999-555-5555',
                    'mailing_address' => [
                        'payee_name' => 'John Brown',
                        'street_address_1' => '60 University Pl',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip' => '10003'
                    ],
                ],
            ],
            'rent' => 700,
            'due_date' => 1,
            'lease_start' => '2015-02-02',
            'lease_end' => '2020-02-02',
        ];
        // Auto-Approve group
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);
        $groupSettings = $group->getGroupSettings();
        $groupSettings->setAutoApproveContracts(true);
        $this->getEntityManager()->flush($groupSettings);

        $response = $this->postRequest($params);
        $this->assertResponse($response, 201);

        $answer = $this->parseContent($response->getContent());
        $tenant = $this->getUser();
        /** Contract $contract */
        $this->assertNotNull(
            $contract = $this->getEntityRepository(self::WORK_ENTITY)->findOneBy(
                [
                    'tenant' => $tenant,
                    'id' => $this->getIdEncoder()->decode($answer['id'])
                ]
            ),
            'Contract was not created'
        );

        $this->assertEquals(
            ContractStatus::APPROVED,
            $contract->getStatus(),
            'New Contract for auto-approve Group should have status \'APPROVED\''
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
                    'rent' => 999,
                    'due_date' => 3,
                    'lease_start' => '2015-01-01',
                    'lease_end' => '2020-03-03',
                    'experian_reporting' => 'enabled',
                ]
            ],
            [
                [
                    'rent' => 555,
                    'due_date' => 3,
                    'lease_start' => '2015-01-01',
                    'lease_end' => '2020-03-03',
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

        $lastContract = $repo->findOneBy([
            'tenant' => $tenant,
        ], ['id' => 'DESC']);

        $encodedId = $this->getIdEncoder()->encode($lastContract->getId());

        $response = $this->putRequest($encodedId, $requestParams);

        $this->assertResponse($response, $statusCode);

        $this->getEm()->refresh($lastContract);

        $this->assertEquals(
            !!$lastContract->getReportToExperian(),
            ReportingType::getMapValue($requestParams['experian_reporting'])
        );
        $this->assertEquals($requestParams['rent'], $lastContract->getRent(), 'Rent was not updated');
        $this->assertEquals($requestParams['due_date'], $lastContract->getDueDate(), 'Due date was not updated');
        $this->assertEquals(
            $requestParams['lease_start'],
            $lastContract->getStartAt()->format('Y-m-d'),
            'Lease start date was not updated'
        );
        $this->assertEquals(
            $requestParams['lease_end'],
            $lastContract->getFinishAt()->format('Y-m-d'),
            'Lease end date was not updated'
        );
    }

    /**
     * @return array
     */
    public static function wrongContractDataProvider()
    {
        return [
            // 0
            [
                self::contractsDataProvider()[4],
                [
                    [
                        'parameter' => 'unit_url',
                        'message' => 'This value is not valid.'
                    ],
                ]
            ],
            // 1
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
                        'parameter' => 'new_unit_landlord_type',
                        'message' => 'api.errors.landlord.type.invalid',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_phone',
                        'value' => '111111111',
                        'message' => 'error.user.phone.format',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_email',
                        'value' => 'test_landlord3gmail.com',
                        'message' => 'This value is not a valid email address.',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_payee_name',
                        'message' => 'api.errors.mailing_address.payee_name.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_street_address_1',
                        'message' => 'api.errors.mailing_address.street_address_1.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_state',
                        'message' => 'api.errors.mailing_address.state.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_city',
                        'message' => 'api.errors.mailing_address.city.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_zip',
                        'message' => 'api.errors.mailing_address.zip.empty',
                    ],
                ]
            ],
            // 2
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
                        'parameter' => 'new_unit_landlord_type',
                        'message' => 'api.errors.landlord.type.invalid',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_payee_name',
                        'message' => 'api.errors.mailing_address.payee_name.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_street_address_1',
                        'message' => 'api.errors.mailing_address.street_address_1.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_state',
                        'message' => 'api.errors.mailing_address.state.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_city',
                        'message' => 'api.errors.mailing_address.city.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_mailing_address_zip',
                        'message' => 'api.errors.mailing_address.zip.empty',
                    ],
                ]
            ],
            // 3
            [
                self::contractsDataProvider()[7],
                [
                    [
                        'message' => 'api.errors.contracts.property.invalid'
                    ],
                ]
            ],
            // 4
            [
                self::contractsDataProvider()[8],
                [
                    [
                        'message' => 'api.errors.contracts.property.not_standalone'
                    ],
                ]
            ],
            // 5
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
            // 6
            [
                self::contractsDataProvider()[11],
                [
                    [
                        'parameter' => 'new_unit_landlord_first_name',
                        'message' => 'api.errors.landlord.first_name.empty',
                    ],
                    [
                        'parameter' => 'new_unit_landlord_last_name',
                        'message' => 'api.errors.landlord.last_name.empty',
                    ],
                ]
            ],
            // 7
            [
                self::contractsDataProvider()[12],
                [
                    [
                        'parameter' => 'new_unit_landlord_company_name',
                        'message' => 'api.errors.landlord.company_name.empty',
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
     */
    public function duplicateUnitErrorOnCreateContract()
    {
        $params = [
            'new_unit' => [
                'address' => [
                    'unit_name' => '1-a',
                    'street' => '770 Broadway',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip' => '10003',
                ],
                'landlord' => [
                    'type' => 'person',
                    'first_name' => 'John',
                    'last_name' => 'Brown',
                    'email' => 'test_landlord4@gmail.com',
                    'phone' => '999-555-5555',
                    'mailing_address' => [
                        'payee_name' => 'John Brown',
                        'street_address_1' => '771 Broadway',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip' => '10003'
                    ],
                ],
            ],
            'rent' => 700,
            'due_date' => 1,
            'lease_start' => '2015-02-02',
            'lease_end' => '2020-02-02',
        ];

        $response = $this->postRequest($params);
        $this->assertResponse($response, 409);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessageRegExp /Request parameter experian_reporting value 'enable' violated a constraint/
     */
    public function wrongEnabledCreate()
    {
        $requestParams = [
            'unit_url' => 'unit_url/2974582658',
            'rent' => '1200.0',
            'due_date' => 1,
            'lease_start' => '2012-12-12',
            'experian_reporting' => 'enable',
        ];

        $this->createContract($requestParams, '400');
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessageRegExp /Request parameter experian_reporting value 'disable' violated a constraint/
     */
    public function wrongEnabledEdit()
    {
        $requestParams = [
            'rent' => '1200.0',
            'due_date' => 1,
            'lease_start' => '2012-12-12',
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
                    'rent' => 555,
                    'due_date' => 3,
                    'lease_start' => '2015-01-01',
                    'lease_end' => '2020-03-03',
                    'experian_reporting' => 'enabled'
                ],
                true,
                'now'
            ],
            [
                [
                    'unit_url' => 'unit_url/2974582658',
                    'rent' => 555,
                    'due_date' => 3,
                    'lease_start' => '2015-01-01',
                    'lease_end' => '2020-03-03',
                ],
                false,
            ],
            [
                [
                    'unit_url' => 'unit_url/2974582658',
                    'rent' => 555,
                    'due_date' => 3,
                    'lease_start' => '2015-01-01',
                    'lease_end' => '2020-03-03',
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
                [
                    'experian_reporting' => 'enabled',
                    'rent' => 555,
                    'due_date' => 3,
                    'lease_start' => '2015-01-01',
                    'lease_end' => '2020-03-03',
                ],
                true,
                'now',
            ],
            [
                [
                    'experian_reporting' => 'disabled',
                    'rent' => 555,
                    'due_date' => 3,
                    'lease_start' => '2015-01-01',
                    'lease_end' => '2020-03-03',
                ],
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
     * @depends setExperianReportingStartAt-0
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

    /**
     * @test
     */
    public function shouldAllowLeaseEndToBeEmpty()
    {
        $requestParams = [
            'unit_url' => 'unit_url/2974582658',
            'rent' => 555,
            'due_date' => 3,
            'lease_start' => '2015-01-01',
            'experian_reporting' => 'enabled'
        ];

        $response = $this->postRequest($requestParams);
        $this->assertResponse($response, 201);
        $answer = $this->parseContent($response->getContent());
        $tenant = $this->getUser();
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $this->assertNotNull(
            $contract = $repo->findOneBy(
                [
                    'tenant' => $tenant,
                    'id' => $this->getIdEncoder()->decode($answer['id'])
                ]
            ),
            'Contract was not created'
        );

        $this->assertEquals($requestParams['rent'], $contract->getRent());
        $this->assertEquals($requestParams['due_date'], $contract->getDueDate());
        $this->assertEquals($requestParams['lease_start'], $contract->getStartAt()->format('Y-m-d'));
        $this->assertNull($contract->getFinishAt(), 'FinishAt is expected to be NULL');
    }

    /**
     * @test
     * This test checks :
     *  - created new contract
     *  - created new dtr group
     *  - created new holding
     *  - created new landlord
     *  - created new trustedLandlord
     *  - added new unit for exist property
     */
    public function shouldCreateFullStructure()
    {
        $partner = $this->getEntityManager()->find('RjDataBundle:Partner', 1);
        $this->assertNotNull($partner, 'Check fixtures, should be exist partner');
        $this->getUser()->setPartner($partner);
        $this->getEntityManager()->flush();

        $groupRepo = $this->getEntityManager()->getRepository('DataBundle:Group');
        $landlordRepo = $this->getEntityManager()->getRepository('RjDataBundle:Landlord');
        $trustedLandlordRepo = $this->getEntityManager()->getRepository('RjDataBundle:TrustedLandlord');
        $unitRepo = $this->getEntityManager()->getRepository('RjDataBundle:Unit');

        $countGroupsBefore = count($groupRepo->findAll());
        $countLandlordsBefore = count($landlordRepo->findAll());
        $countTrustedLandlordsBefore = count($trustedLandlordRepo->findAll());
        $countUnitsBefore = count($unitRepo->findAll());

        $params = [
            'new_unit' => [
                'address' => [
                    'unit_name' => '10001-a',
                    'street' => '770 Broadway',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip' => '10003',
                ],
                'landlord' => [
                    'type' => 'company',
                    'first_name' => 'John',
                    'last_name' => 'Brown',
                    'company_name' => 'John Brown Ltd.',
                    'email' => 'test_landlord1001@landlord.com',
                    'phone' => '999-555-5555',
                    'mailing_address' => [
                        'payee_name' => 'John Brown Ltd.',
                        'street_address_1' => '771 Broadway',
                        'street_address_2' => '#444',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip' => '10003'
                    ],
                ],
            ],
            'rent' => 700,
            'due_date' => 1,
            'lease_start' => '2015-02-02',
            'lease_end' => '2020-02-02',
        ];

        $response = $this->postRequest($params);
        $this->assertResponse($response, 201);

        $trustedLandlordsAfter = $trustedLandlordRepo->findAll();
        $this->assertCount(
            $countTrustedLandlordsBefore + 1,
            $trustedLandlordsAfter,
            'Should be created new trusted landlord'
        );
        $groupsAfter = $groupRepo->findAll();
        $this->assertCount(
            $countGroupsBefore + 1,
            $groupsAfter,
            'Should be created new group'
        );
        $landlordsAfter = $landlordRepo->findAll();
        $this->assertCount(
            $countLandlordsBefore + 1,
            $landlordsAfter,
            'Should be created new landlord'
        );
        $unitsAfter = $unitRepo->findAll();
        $this->assertCount(
            $countUnitsBefore + 1,
            $unitsAfter,
            'Should be created new unit'
        );
        /** @var TrustedLandlord $newTrustedLandlord */
        $newTrustedLandlord = end($trustedLandlordsAfter);
        $this->assertEquals(
            TrustedLandlordType::COMPANY,
            $newTrustedLandlord->getType(),
            'Should be created company trusted landlord'
        );
        $this->assertEquals(
            'John Brown Ltd.',
            $newTrustedLandlord->getCompanyName(),
            'Should be set company_name to "John Brown Ltd." on trusted landlord'
        );
        $this->assertEquals(
            'John',
            $newTrustedLandlord->getFirstName(),
            'Should be set first_name to John on trusted landlord'
        );
        $this->assertEquals(
            'Brown',
            $newTrustedLandlord->getLastName(),
            'Should be set last_name to Brown on trusted landlord'
        );
        $this->assertEquals(
            '9995555555',
            $newTrustedLandlord->getPhone(),
            'Should be set phone to "9995555555" on trusted landlord'
        );

        $this->assertNotNull($newTrustedLandlord->getCheckMailingAddress(), 'Should be created check_mailing_address');

        $this->assertEquals(
            'John Brown Ltd.',
            $newTrustedLandlord->getCheckMailingAddress()->getAddressee(),
            'Should be set addressee to "John Brown Ltd." on check mailing address'
        );
        $this->assertEquals(
            '771 Broadway',
            $newTrustedLandlord->getCheckMailingAddress()->getAddress1(),
            'Should be set address1 to "771 Broadway" on check mailing address'
        );
        $this->assertEquals(
            '#444',
            $newTrustedLandlord->getCheckMailingAddress()->getAddress2(),
            'Should be set address2 to "#444" on check mailing address'
        );
        $this->assertEquals(
            'New York',
            $newTrustedLandlord->getCheckMailingAddress()->getCity(),
            'Should be set city to "New York" on check mailing address'
        );
        $this->assertEquals(
            'NY',
            $newTrustedLandlord->getCheckMailingAddress()->getState(),
            'Should be set state to "NY" on check mailing address'
        );
        $this->assertEquals(
            '10003',
            $newTrustedLandlord->getCheckMailingAddress()->getZip(),
            'Should be set zip to "10003" on check mailing address'
        );
        /** @var Landlord $newLandlord */
        $newLandlord = end($landlordsAfter);
        $this->assertEquals(
            'John',
            $newLandlord->getFirstName(),
            'Should be set first_name to John on landlord'
        );
        $this->assertEquals(
            'Brown',
            $newLandlord->getLastName(),
            'Should be set last_name to Brown on landlord'
        );
        $this->assertEquals(
            '9995555555',
            $newLandlord->getPhone(),
            'Should be set phone to "9995555555" on landlord'
        );
        $this->assertEquals(
            'test_landlord1001@landlord.com',
            $newLandlord->getEmail(),
            'Should be set phone to "test_landlord1001@landlord.com" on landlord'
        );
        $this->assertNotNull($newLandlord->getPartner(), 'Should be added partner for landlord');
        /** @var Group $newGroup */
        $newGroup = end($groupsAfter);
        $this->assertEquals(
            $newTrustedLandlord->getCompanyName(),
            $newGroup->getName(),
            'Should be set name to trusted landlord company name on group'
        );
        $this->assertEquals(
            GroupType::RENT,
            $newGroup->getType(),
            'Should be set type to rent on group'
        );
        $this->assertEquals(
            OrderAlgorithmType::PAYDIRECT,
            $newGroup->getOrderAlgorithm(),
            'Should be created dtr group'
        );
        $this->assertNotNull(
            $newGroup->getGroupSettings(),
            'Should be created group settings for group'
        );
        $this->assertEquals(
            PaymentProcessor::ACI,
            $newGroup->getGroupSettings()->getPaymentProcessor(),
            'Should be set payment processor "ACI" on group settings'
        );
        $this->assertTrue(
            $newGroup->getGroupSettings()->isAutoApproveContracts(),
            'Should be created auto approved contracts group'
        );
        $this->assertTrue(
            $newGroup->getGroupSettings()->isPassedAch(),
            'Should be created passed ach group'
        );
        $this->assertEquals(
            $this->getContainer()->getParameter('paydirect_fee_cc'),
            $newGroup->getGroupSettings()->getFeeCC(),
            'Should be set default fee cc on group settings'
        );
        $this->assertEquals(
            $this->getContainer()->getParameter('paydirect_fee_ach'),
            $newGroup->getGroupSettings()->getFeeACH(),
            'Should be set default fee ach on group settings'
        );
        $this->assertEquals(
            $this->getContainer()->getParameter('dod_limit_max_payment'),
            $newGroup->getGroupSettings()->getMaxLimitPerMonth(),
            'Should be set default max_limit_per_month on group settings'
        );
        $depositAccount = $newGroup->getRentDepositAccountForCurrentPaymentProcessor();
        $this->assertNotNull(
            $depositAccount,
            'Should be created new deposit account for group'
        );
        $this->assertEquals(
            $this->getContainer()->getParameter('aci.collect_pay.pay_direct_escrow_account'),
            $depositAccount->getMerchantName(),
            'Should be set merchant name to default on deposit account'
        );
        $this->assertEquals(
            DepositAccountStatus::DA_COMPLETE,
            $depositAccount->getStatus(),
            'Should be set status to complete on deposit account'
        );
    }
}
