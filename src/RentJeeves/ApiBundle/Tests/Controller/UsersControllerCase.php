<?php
namespace RentJeeves\ApiBundle\Tests\Controller;

use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;

class UsersControllerCase extends BaseApiTestCase
{
    const USER_ACCESS_TOKEN = 'test_partner';
    const WORK_ENTITY = 'RjDataBundle:Tenant';
    const URL_PREFIX = '/api/partner';
    const REQUEST_URL = 'users';

    /** @var string */
    protected $userEmail = 'anna_lee@example.com';

    /**
     * @return array
     */
    public function createTenantDataNegativeProvider()
    {
        return [
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Tom',
                    'last_name' => 'Ford',
                    'email' => 'tom.ford@mail.com',
                    'password' => ''
                ],
                'api.errors.user.password_required',
            ],
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Tenant',
                    'last_name' => 'Epic',
                    'email' => 'tenant11@example.com',
                    'password' => 'pass111aaaa',
                    'holding_id' => '11111111', // holding is invalid
                    'resident_id' => '1111111',
                ],
                'This value is not valid.',
            ],
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Tenant',
                    'last_name' => 'Epic',
                    'email' => 'tenant11@example.com',
                    'password' => 'pass111aaaa',
                    'holding_id' => '5',
                ],
                'api.errors.tenant.resident_id.empty',
            ],
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Tenant',
                    'last_name' => 'Epic',
                    'email' => 'tenant11@example.com',
                    'password' => 'pass111aaaa',
                    'resident_id' => 'rt1111',
                ],
                'api.errors.tenant.holding_id.empty',
            ],
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Tenant',
                    'last_name' => 'Epic',
                    'email' => 'tenant11@example.com',
                    'password' => 'pass111aaaa',
                ],
                false,
                409
            ],
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Tenant',
                    'last_name' => 'Epic',
                    'email' => 'tenant12@example.com',
                    'password' => 'pass111aaaa',
                    'holding_id' => 5,
                    'resident_id' => 't0013534'
                ],
                false,
                409
            ],
        ];
    }

    /**
     * @param array  $requestParams
     * @param string $errorMessage
     * @param int    $statusCode
     *
     * @test
     * @dataProvider createTenantDataNegativeProvider
     */
    public function errorWhenCreatingUser($requestParams, $errorMessage, $statusCode = 400)
    {
        $response = $this->postRequest($requestParams);

        $this->assertResponse($response, $statusCode);

        if ($errorMessage) {
            $answer = $this->parseContent($response->getContent());
            $this->assertTrue(isset($answer[0]['message']), 'Should retrieve error message');
            $this->assertEquals($errorMessage, $answer[0]['message'], 'Invalid error message');
        }
    }

    /**
     * @return array
     */
    public function createTenantDataPositiveProvider()
    {
        return [
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Tom',
                    'last_name' => 'Ford',
                    'email' => 'tom.ford@mail.com',
                    'password' => '123450000000'
                ],
            ],
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Max',
                    'last_name' => 'Anderson',
                    'email' => 'maxx@mail.com',
                    'password' => '321321321321'
                ],
            ],
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Tenant',
                    'last_name' => 'Epic',
                    'email' => 'tenant12@example.com',
                    'password' => 'pass111aaaa',
                    'holding_id' => 5,
                    'resident_id' => 't0013535'
                ],
                true
            ]
        ];
    }

    /**
     * @param array $requestParams
     * @param int   $statusCode
     * @param bool  $checkResidentMapping
     *
     * @test
     * @dataProvider createTenantDataPositiveProvider
     */
    public function createUser($requestParams, $checkResidentMapping = false, $statusCode = 201)
    {
        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $this->assertNull($repo->findOneBy(['email' => $requestParams['email']]), 'User should not exist before.');

        $response = $this->postRequest($requestParams);

        $this->assertResponse($response, $statusCode);

        $answer = $this->parseContent($response->getContent());

        /** @var Tenant $tenant */
        $this->assertNotNull(
            $tenant = $repo->findOneBy(['email' => $requestParams['email']]),
            'Should be created new user with requested email'
        );
        $this->assertEquals($tenant->getId(), $this->getIdEncoder()->decode($answer['id']));

        if ($checkResidentMapping) {
            $this->assertCount(
                1,
                $residentMappings = $tenant->getResidentsMapping(),
                'Should be created new resident mapping for this user'
            );
            /** @var ResidentMapping $residentMapping */
            $residentMapping = $residentMappings->first();

            $this->assertNotNull($holding = $residentMapping->getHolding(), 'Should be set holding');

            $this->assertEquals($holding->getId(), $requestParams['holding_id'], 'Holding is incorrect');

            $this->assertEquals(
                $residentMapping->getResidentId(),
                $requestParams['resident_id'],
                'ResidentId is incorrect'
            );
        }
    }
}
