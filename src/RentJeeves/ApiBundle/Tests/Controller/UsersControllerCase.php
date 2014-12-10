<?php
namespace RentJeeves\ApiBundle\Tests\Controller;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;

class UsersControllerCase extends BaseApiTestCase
{
    const USER_ACCESS_TOKEN = 'test_partner';
    const WORK_ENTITY = 'RjDataBundle:Tenant';
    const URL_PREFIX = '/api/partner';
    const REQUEST_URL = 'users';

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
        ];
    }

    /**
     * @test
     * @dataProvider createTenantDataNegativeProvider
     */
    public function errorWhenCreatingUser($requestParams, $errorMessage, $format = 'json', $statusCode = 400)
    {
        $this->prepareClient();

        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);

        $this->assertEquals($errorMessage, $answer[0]['message']);
    }

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
        ];
    }

    /**
     * @test
     * @dataProvider createTenantDataPositiveProvider
     */
    public function createUser($requestParams, $format = 'json', $statusCode = 201)
    {
        $this->prepareClient();

        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);
        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $this->assertNotNull($tenant = $repo->findOneBy(['email' => $requestParams['email']]));
        $this->assertEquals($tenant->getId(), $this->getIdEncoder()->decode($answer['id']));
    }
}
