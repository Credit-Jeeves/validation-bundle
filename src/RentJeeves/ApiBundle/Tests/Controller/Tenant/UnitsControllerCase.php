<?php

namespace RentJeeves\ApiBundle\Tests\Controller\Tenant;

use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitRepository;

class UnitsControllerCase extends BaseApiTestCase
{
    const WORK_ENTITY = 'RjDataBundle:Unit';

    const REQUEST_URL = 'units';

    /**
     * @return array
     */
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
     * @param array $requestParams
     * @param array $dbRequest
     *
     * @test
     * @dataProvider getUnitsDataProvider
     */
    public function getUnits($requestParams, $dbRequest)
    {
        $response = $this->getRequest(null, $requestParams);

        $this->assertResponse($response);

        /** @var UnitRepository $repo */
        $repo = $this->getEntityRepository(self::WORK_ENTITY);
        /** @var Unit[] $result */
        $result = $repo->getUnitsByAddress($dbRequest);

        $answer = $this->parseContent($response->getContent());

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
     * @param array $requestParams
     * @param int   $statusCode
     *
     * @test
     * @dataProvider getEmptyUnitsDataProvider
     */
    public function getEmptyUnits($requestParams, $statusCode = 204)
    {
        $response = $this->getRequest(null, $requestParams);

        $this->assertResponse($response, $statusCode);
    }
}
