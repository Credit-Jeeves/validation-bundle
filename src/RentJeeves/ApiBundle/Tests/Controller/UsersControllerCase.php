<?php
namespace RentJeeves\ApiBundle\Tests\Controller;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;

class UsersControllerCase extends BaseApiTestCase
{
    const PARTNER_USER_ACCESS_TOKEN = 'test_partner';
    const WORK_ENTITY = 'RjDataBundle:Tenant';
    const URL_PREFIX = '/api/partner';

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
                400,
                'api.errors.user.password_required'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createTenantDataNegativeProvider
     */
    public function errorWhenCreatingUser($requestParams, $statusCode, $errorMessage, $format = 'json')
    {
        $client = $this->getClient();

        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'POST',
            self::URL_PREFIX . "/users.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . self::PARTNER_USER_ACCESS_TOKEN,
            ],
            $serializer->serialize($requestParams, $format)
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);

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
                201
            ],
            [
                [
                    'type' => 'tenant',
                    'first_name' => 'Max',
                    'last_name' => 'Anderson',
                    'email' => 'maxx@mail.com',
                    'password' => '321321321321'
                ],
                201
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createTenantDataPositiveProvider
     */
    public function createUser($requestParams, $statusCode, $format = 'json')
    {
        $client = $this->getClient();

        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'POST',
            self::URL_PREFIX . "/users.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . self::PARTNER_USER_ACCESS_TOKEN,
            ],
            $serializer->serialize($requestParams, $format)
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);
        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $this->assertNotNull($tenant = $repo->findOneBy(['email' => $requestParams['email']]));
        $this->assertEquals($tenant->getId(), $this->getIdEncoder()->decode($answer['id']));
    }
}
