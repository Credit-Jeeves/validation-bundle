<?php

namespace RentJeeves\ApiBundle\Tests\RestApi\Tenant;

use RentJeeves\ApiBundle\Tests\BaseApiTestCase;

class UnitsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Unit';

    public static function getUnitsDataProvider()
    {
        return [
            [
                'json',
                [
                    'street' => '116 Lexington Avenue',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10016'
                ],
                200,
                [
                    'street' => 'Lexington Avenue',
                    'number' => '116',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10016'
                ]
            ],
            [
                'json',
                [
                    'street' => '770 Broadway',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10003'
                ],
                200,
                [
                    'street' => 'Broadway',
                    'number' => '770',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10003'
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getUnitsDataProvider
     */
    public function getUnits($format, $requestParams, $statusCode, $dbRequest)
    {
        $client = $this->getClient();

        $client->request(
            'GET',
            self::URL_PREFIX . "/units.{$format}",
            $requestParams,
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $repo = $this->getEntityRepository(self::WORK_ENTITY);


        $result = $repo->getUnitsByAddress($dbRequest);

        $answer = $this->parseContent($client->getResponse()->getContent(), $format);

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

    public static function getEmptyUnitsDataProvider()
    {
        return [
            [
                'json',
                [
                    'street' => '1T Test',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10001'
                ],
                204
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getEmptyUnitsDataProvider
     */
    public function getEmptyUnits($format, $requestParams, $statusCode)
    {
        $client = $this->getClient();

        $client->request(
            'GET',
            self::URL_PREFIX . "/units.{$format}",
            $requestParams,
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::TENANT_ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);
    }
}
