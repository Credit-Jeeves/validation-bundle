<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentAccountType;

class PaymentAccountsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:PaymentAccount';

    const REQUEST_URL = 'payment_accounts';

    /**
     * @return array
     */
    public static function getEmptyPaymentAccountsDataProvider()
    {
        return [
            ['alex@rentrack.com'],
        ];
    }

    /**
     * @param string $email
     * @param string $format
     * @param int    $statusCode
     *
     * @test
     * @dataProvider getEmptyPaymentAccountsDataProvider
     */
    public function getEmptyPaymentAccounts($email, $format = 'json', $statusCode = 204)
    {
        $this->setUserEmail($email);

        $response = $this->getRequest(null, [], $format);

        $this->assertResponse($response, $statusCode, $format);
    }

    /**
     * @return array
     */
    public static function getPaymentAccountsDataProvider()
    {
        return [
            ['tenant11@example.com'],
        ];
    }

    /**
     * @param string $email
     * @param string $format
     * @param int    $statusCode
     *
     * @test
     * @dataProvider getPaymentAccountsDataProvider
     */
    public function getPaymentAccounts($email, $format = 'json', $statusCode = 200)
    {
        $this->setUserEmail($email);

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getUser();
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

    /**
     * @return array
     */
    public function getPaymentAccountDataProvider()
    {
        return [
            [
                '656765400',
                [
                    'id' => '656765400',
                    'url' => $this->prepareUrl(656765400, false, 'payment_accounts', true),
                    'nickname' => 'Card',
                    'type' => PaymentAccountType::CARD,
                    'expiration' => (new \DateTime('+1 month'))->format('Y-m'),
                    'billing_address_url' => $this->prepareUrl(2539807809, false, 'addresses', true),
                ]
            ],
            [
                '1758512013',
                [
                    'id' => '1758512013',
                    'url' => $this->prepareUrl(1758512013, false, 'payment_accounts', true),
                    'nickname' => 'Bank',
                    'type' => PaymentAccountType::BANK,
                    'billing_address_url' => '',
                ]
            ]
        ];
    }

    /**
     * @param $paymentAccountEncodedId
     * @param $result
     *
     * @test
     * @dataProvider getPaymentAccountDataProvider
     */
    public function getPaymentAccount($paymentAccountEncodedId, $result)
    {
        $response = $this->getRequest($paymentAccountEncodedId);

        $this->assertResponse($response);

        $this->assertResponseContent($response->getContent(), $result);
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
     * @param array  $requestParams
     * @param string $format
     * @param int    $statusCode
     *
     * @test
     * @dataProvider createPaymentAccountDataProvider
     */
    public function createPaymentAccount($requestParams, $format = 'json', $statusCode = 201)
    {
        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $answer = $this->parseContent($response->getContent(), $format);

        $tenant = $this->getUser();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $this->assertNotNull(
            $repo->findOneBy([
                'user' => $tenant,
                'id' => $this->getIdEncoder()->decode($answer['id'])
            ])
        );
    }

    /**
     * @return array
     */
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
     * @param array  $requestParams
     * @param string $format
     * @param int    $statusCode
     *
     * @test
     * @dataProvider editPaymentAccountDataProvider
     */
    public function editPaymentAccount($requestParams, $format = 'json', $statusCode = 204)
    {
        $tenant = $this->getUser();

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

    /**
     * @return array
     */
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
     * @param array  $requestParams
     * @param array  $result
     * @param string $format
     * @param int    $statusCode
     *
     * @test
     * @dataProvider wrongPaymentAccountDataProvider
     */
    public function wrongEditPaymentAccount($requestParams, $result, $format = 'json', $statusCode = 400)
    {
        $tenant = $this->getUser();

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
     * @param array  $requestParams
     * @param array  $result
     * @param string $format
     * @param int    $statusCode
     *
     * @test
     * @dataProvider wrongPaymentAccountDataProvider
     */
    public function wrongCreatePaymentAccount($requestParams, $result, $format = 'json', $statusCode = 400)
    {
        $response = $this->postRequest($requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $this->assertResponseContent($response->getContent(), $result, $format);
    }
}
