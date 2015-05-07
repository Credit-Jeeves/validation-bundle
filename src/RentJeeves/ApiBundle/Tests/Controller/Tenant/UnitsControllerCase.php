<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use RentJeeves\ApiBundle\Tests\BaseApiTestCase;

class UnitsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Unit';

    const REQUEST_URL = 'units';

    public static function getUnitsDataProvider()
    {
        return [
            [
                [
                    'street' => '116 Lexington Avenue',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10016'
                ],
                [
                    'street' => 'Lexington Avenue',
                    'number' => '116',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10016'
                ]
            ],
            [
                [
                    'street' => '770 Broadway',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10003'
                ],
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
    public function getUnits($requestParams, $dbRequest, $format = 'json', $statusCode = 200)
    {
        $response = $this->getRequest(null, $requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);

        $repo = $this->getEntityRepository(self::WORK_ENTITY);

        $result = $repo->getUnitsByAddress($dbRequest);

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

    public static function getEmptyUnitsDataProvider()
    {
        return [
            [
                [
                    'street' => '1T Test',
                    'state' => 'NY',
                    'city' => 'New York',
                    'zip' => '10001'
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getEmptyUnitsDataProvider
     */
    public function getEmptyUnits($requestParams, $format = 'json', $statusCode = 204)
    {
        $response = $this->getRequest(null, $requestParams, $format);

        $this->assertResponse($response, $statusCode, $format);
    }
}
