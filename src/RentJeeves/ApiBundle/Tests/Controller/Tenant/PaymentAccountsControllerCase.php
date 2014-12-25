<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\PaymentAccount;

class PaymentAccountsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:PaymentAccount';

    const REQUEST_URL = 'payment_accounts';

    public static function getEmptyPaymentAccountsDataProvider()
    {
        return [
            ['alex@rentrack.com'],
        ];
    }

    /**
     * @test
     * @dataProvider getEmptyPaymentAccountsDataProvider
     */
    public function getEmptyPaymentAccounts($email, $format = 'json', $statusCode = 204)
    {
        $this->setTenantEmail($email);

        $this->prepareClient();

        $response = $this->getRequest(null, [], $format);

        $this->assertResponse($response, $statusCode, $format);
    }

    public static function getPaymentAccountsDataProvider()
    {
        return [
            ['tenant11@example.com'],
        ];
    }

    /**
     * @test
     * @dataProvider getPaymentAccountsDataProvider
     */
    public function getPaymentAccounts($email, $format = 'json', $statusCode = 200)
    {
        $this->setTenantEmail($email);

        $this->prepareClient();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getTenant();
        $result = $repo->findBy(['user' => $tenant]);

        $response = $this->getRequest(null, [], $format);

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);

        $this->assertEquals(count($result), count($answer));

        // check first and last element
        $this->assertEquals(
            $result[0]->getId(),
            $this->getIdEncoder()->decode($answer[0]['id'])
        );

        $this->assertEquals(
            $result[0]->getId(),
            $this->getUrlEncoder()->decode($answer[0]['url'])
        );

        $this->assertEquals(
            $result[count($result)-1]->getId(),
            $this->getUrlEncoder()->decode($answer[count($answer) -1]['url'])
        );

        $this->assertEquals(
            $this->getIdEncoder()->encode($result[count($result)-1]->getId()),
            $answer[count($answer) -1]['id']
        );
    }

    public static function paymentAccountsDataProvider()
    {
        return [
            [
                'contract_url' => 'contract_url/656765400',
                'type' =>  'card',
                'nickname' => 'Card Test 1',
                'name' => 'Card Name',
                'card' => [
                    'account' => '4111111111111111',
                    'expiration' => '2025-01',
                    'billing_address' => [
                        'street' => '320 Test Street',
                        'city' => 'Test City',
                        'state' => 'NY',
                        'zip' => '9001',
                    ],
                    'cvv' => '123',
                ],
            ],
            [
                'contract_url' => 'contract_url/656765400',
                'type' =>  'card',
                'nickname' => 'Card Test 2',
                'name' => 'Card Name',
                'card' => [
                    'account' => '4111111111111110',
                    'expiration' => '2014-01',
                    'billing_address' => [
                        'street' => '320 Test Street',
                        'state' => 'NY',
                        'zip' => '9001',
                    ],
                    'cvv' => '123444',
                ],
            ],
            [
                'contract_url' => 'contract_url/656765400',
                'type' =>  'bank',
                'nickname' => 'Bank Test 1',
                'name' => 'Bank Name',
                'bank' => [
                    'type' => 'test'
                ],
            ],
            [
                'contract_url' => 'contract_url/656765400',
                'type' =>  'bank',
                'nickname' => 'Bank Test 2',
                'name' => 'Bank Name',
                'bank' => [
                    'type' => 'checking',
                    'routing' => '062202574',
                    'account' => '123245678',
                ],
            ],
        ];
    }

    public static function createPaymentAccountDataProvider()
    {
        return [
            [
                self::paymentAccountsDataProvider()[0],
            ],
            [
                self::paymentAccountsDataProvider()[3]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createPaymentAccountDataProvider
     */
    public function createPaymentAccount($requestParams, $format = 'json', $statusCode = 201)
    {
        $this->prepareClient();

        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);

        $tenant = $this->getTenant();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $this->assertNotNull(
            $repo->findOneBy([
                'user' => $tenant,
                'id' => $this->getIdEncoder()->decode($answer['id'])
            ])
        );
    }

    public static function editPaymentAccountDataProvider()
    {
        return [
            [
                self::paymentAccountsDataProvider()[0]
            ],
            [
                self::paymentAccountsDataProvider()[3]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider editPaymentAccountDataProvider
     */
    public function editPaymentAccount($requestParams, $format = 'json', $statusCode = 204)
    {
        $this->prepareClient();

        $tenant = $this->getTenant();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        /** @var PaymentAccount $last */
        $last = $repo->findOneBy([
            'user' => $tenant,
        ], ['id' => 'DESC']);

        $encodedId = $this->getIdEncoder()->encode($last->getId());

        $response = $this->putRequest($encodedId, $requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $this->getEm()->refresh($last);

        $this->assertEquals($last->getType(), $requestParams['type']);
        $this->assertEquals($last->getName(), $requestParams['nickname']);
        $this->assertNotNull($last->getToken());
    }

    public static function wrongPaymentAccountDataProvider()
    {
        return [
            [
                self::paymentAccountsDataProvider()[1],
                [
                    [
                        'parameter' => 'card_account',
                        'value' => '4111111111111110',
                        'message' => 'api.errors.payment_accounts.card.account.checksum'
                    ],
                    [
                        'parameter' => 'card_expiration',
                        'value' => '2014-01',
                        'message' => 'api.errors.payment_accounts.card.expiration.invalid_expiration'
                    ],
                    [
                        'parameter' => 'card_billing_address_city',
                        'message' => 'error.user.city.empty'
                    ],
                    [
                        'parameter' => 'card_cvv',
                        'value' => '123444',
                        'message' => 'api.errors.payment_accounts.card.cvv'
                    ],
                ]
            ],
            [
                self::paymentAccountsDataProvider()[2],
                [
                    [
                        'parameter' => 'bank_routing',
                        'message' => 'This value should not be blank.'
                    ],
                    [
                        'parameter' => 'bank_account',
                        'message' => 'This value should not be blank.'
                    ],
                    [
                        'parameter' => 'bank_type',
                        'value' => 'test',
                        'message' => 'api.errors.payment_accounts.bank.type'
                    ],
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider wrongPaymentAccountDataProvider
     */
    public function wrongEditPaymentAccount($requestParams, $result, $format = 'json', $statusCode = 400)
    {
        $this->prepareClient();

        $tenant = $this->getTenant();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $last = $repo->findOneBy([
            'user' => $tenant,
        ], ['id' => 'DESC']);

        $encodedId = $this->getIdEncoder()->encode($last->getId());

        $response = $this->putRequest($encodedId, $requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $this->assertResponseContent($response->getContent(), $result, $format);
    }

    /**
     * @test
     * @dataProvider wrongPaymentAccountDataProvider
     */
    public function wrongCreatePaymentAccount($requestParams, $result, $format = 'json', $statusCode = 400)
    {
        $this->prepareClient();

        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $this->assertResponseContent($response->getContent(), $result, $format);
    }
}
