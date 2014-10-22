<?php

namespace RentJeeves\ApiBundle\Tests\RestApi\Tenant;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\PaymentAccount;

class PaymentAccountsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:PaymentAccount';

    public static function getPaymentAccountsDataProvider()
    {
        return [
            ['json', 200, 204],
        ];
    }

    /**
     * @test
     * @dataProvider getPaymentAccountsDataProvider
     */
    public function getPaymentAccounts($format, $statusCodeFound, $statusCodeNotFound)
    {
        $client = $this->getClient();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        $tenant = $this->getTenant();
        $result = $repo->findBy(['user' => $tenant]);

        $client->request(
            'GET',
            self::URL_PREFIX . "/payment_accounts.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ]
        );

        $statusCode = (count($result) > 0) ?  $statusCodeFound : $statusCodeNotFound;

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);

        $this->assertEquals(count($result), count($answer));

        // check first and last element
        if (count($result) > 0) {
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
                'json',
                201,
                self::paymentAccountsDataProvider()[0],
            ],
            [
                'json',
                400,
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
                        'value' => null,
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
                'json',
                400,
                self::paymentAccountsDataProvider()[2],
                [
                    [
                        'parameter' => 'bank_routing',
                        'value' => null,
                        'message' => 'This value should not be blank.'
                    ],
                    [
                        'parameter' => 'bank_account',
                        'value' => null,
                        'message' => 'This value should not be blank.'
                    ],
                    [
                        'parameter' => 'bank_type',
                        'value' => 'test',
                        'message' => 'api.errors.payment_accounts.bank.type'
                    ],
                ]
            ],
            [
                'json',
                201,
                self::paymentAccountsDataProvider()[3]
            ]
        ];
    }



    /**
     * @test
     * @dataProvider createPaymentAccountDataProvider
     */
    public function createPaymentAccount($format, $statusCode, $requestParams, $result = null)
    {
        $client = $this->getClient();

        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'POST',
            self::URL_PREFIX . "/payment_accounts.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ],
            $serializer->serialize($requestParams, $format)
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        if ($result) {
            $this->assertResponseContent($client->getResponse()->getContent(), $result, $format);
        } else {
            $answer = $this->parseContent($client->getResponse()->getContent(), $format);

            $tenant = $this->getTenant();

            $repo = $this->getEntityRepository(self::WORK_ENTITY);

            $this->assertNotNull(
                $repo->findOneBy([
                    'user' => $tenant,
                    'id' => $this->getIdEncoder()->decode($answer['id'])
                ])
            );
        }
    }

    public static function editPaymentAccountDataProvider()
    {
        return [
            [
                'json',
                204,
                self::paymentAccountsDataProvider()[0]
            ],
            self::createPaymentAccountDataProvider()[1],
            self::createPaymentAccountDataProvider()[2],
            [
                'json',
                204,
                self::paymentAccountsDataProvider()[3]
            ]
        ];
    }

    /**
     * @test
     * @depends createPaymentAccount
     * @dataProvider editPaymentAccountDataProvider
     */
    public function editPaymentAccount($format, $statusCode, $requestParams, $result = null)
    {
        $client = $this->getClient();

        $tenant = $this->getTenant();

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $last = $repo->findOneBy([
            'user' => $tenant,
        ], ['id' => 'DESC']);

        $encodedId = $this->getIdEncoder()->encode($last->getId());
        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'PUT',
            self::URL_PREFIX . "/payment_accounts/{$encodedId}.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ],
            $serializer->serialize($requestParams, $format)
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        if ($result) {
            $this->assertResponseContent($client->getResponse()->getContent(), $result, $format);
        } else {
            /** @var PaymentAccount $payment */
            $payment = $repo->findOneBy([
                'user' => $tenant, 'id' => $last->getId()
            ], ['id' => 'DESC']);

            $this->assertEquals($payment->getType(), $requestParams['type']);
            $this->assertEquals($payment->getName(), $requestParams['nickname']);
            $this->assertNotNull($payment->getToken());
        }
    }
}
