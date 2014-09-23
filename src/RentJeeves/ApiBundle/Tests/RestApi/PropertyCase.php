<?php

namespace RentJeeves\ApiBundle\Tests\RestApi;

use JMS\Serializer\Serializer;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class Properties extends BaseApiTestCase
{
    public static function dataProviderGetProperty()
    {
        return [
            [
                1,
                'json',
                [
                    'status' => 'OK',
                    'property' => [
                        'id' => 1,
                        'address' => [
                            'country' => 'US',
                            'area' => 'NY',
                            'city' => 'New York',
                            'district' => 'Manhattan',
                            'street' => 'Broadway',
                            'number' => '770',
                            'zip' => '10003',
                        ],
                        'full_address' => '770 Broadway, Manhattan, New York, NY 10003',
                        'lan' => -73.9913642,
                        'lat' => 40.7308443,
                        'is_single' => false,
                        'unit_count' => 15,
                    ]
                ],
            ],
            [
                2,
                'json',
                [
                    'status' => 'OK',
                    'property' => [
                        'id' => 2,
                        'address' => [
                            'country' => 'US',
                            'area' => 'CA',
                            'city' => 'Santa Barbara',
                            'district' => null,
                            'street' => 'Andante Rd',
                            'number' => '960',
                            'zip' => '93105',
                        ],
                        'full_address' => '960 Andante Rd, Santa Barbara, CA 93105',
                        'lan' => -119.709369,
                        'lat' => 34.44943,
                        'is_single' => false,
                        'unit_count' => 12,
                    ]
                ],
            ],
            [
                0,
                'json',
                '',
                Response::HTTP_NO_CONTENT,
            ],
        ];
    }

    /**
     * @param $propertyId
     * @param $format
     * @param $result
     * @param int $statusCode
     *
     * @test
     * @dataProvider dataProviderGetProperty
     */
    public function getProperty($propertyId, $format, $result, $statusCode = Response::HTTP_OK)
    {
        $client = $this->getClient();

        $client->request(
            'GET',
            "/api/properties/{$propertyId}.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $this->assertResponseContent($client->getResponse()->getContent(), $result, $format);

    }

    public static function dataProviderSearchByAddress()
    {
        return [
            [
                '960 Andante Rd, Santa Barbara, CA 93105',
                'json',
                [
                    'status' => 'OK',
                    'property' => [
                        'id' => 2,
                        'address' => [
                            'country' => 'US',
                            'area' => 'CA',
                            'city' => 'Santa Barbara',
                            'district' => null,
                            'street' => 'Andante Rd',
                            'number' => '960',
                            'zip' => '93105',
                        ],
                        'full_address' => '960 Andante Rd, Santa Barbara, CA 93105',
                        'lan' => -119.709369,
                        'lat' => 34.44943,
                        'is_single' => false,
                        'unit_count' => 12,
                    ]
                ],
            ],
            [
                '959 Andante Rd, Santa Barbara, CA 93105',
                'json',
                '',
                Response::HTTP_NO_CONTENT,
            ],
            [
                'Test Not Found Address',
                'json',
                [
                    'status' => 'Error',
                    'status_code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Address is invalid'
                ],
                Response::HTTP_BAD_REQUEST,
            ]
        ];
    }

    /**
     * @param $fullAddress
     * @param $format
     * @param $result
     * @param int $statusCode
     *
     * @test
     * @dataProvider dataProviderSearchByAddress
     */
    public function searchProperty($fullAddress, $format, $result, $statusCode = Response::HTTP_OK)
    {
        $client = $this->getClient();

        $client->request(
            'GET',
            "/api/properties/search.{$format}",
            ['address' => $fullAddress],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $this->assertResponseContent($client->getResponse()->getContent(), $result, $format);
    }

    public static function dataProviderGetUnit()
    {
        return [
            [
                [1,1],
                'json',
                [
                    'status' => 'OK',
                    'unit' => [
                        'id' => 1,
                        'unit_name' => '1-a',
                        'property_id' => 1,
                        'has_landlord' => true,
                    ]
                ],
            ],
            [
                [0,0],
                'json',
                '',
                Response::HTTP_NO_CONTENT
            ]
        ];
    }

    /**
     * @param $params
     * @param $format
     * @param $result
     * @param int $statusCode
     *
     * @test
     * @dataProvider dataProviderGetUnit
     */
    public function getUnit($params, $format, $result, $statusCode = Response::HTTP_OK)
    {
        $client = $this->getClient();

        $client->request(
            'GET',
            "/api/properties/{$params[0]}/units/$params[1].{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $this->assertResponseContent($client->getResponse()->getContent(), $result, $format);
    }

    public static function dataProviderGetUnits()
    {
        return [
            [
                1,
                'json',
                [
                    'status' => 'OK',
                    'units' => [
                        [
                            'id' => 1,
                            'unit_name' => '1-a',
                        ]
                    ]
                ],
            ],
            [
                0,
                'json',
                '',
                Response::HTTP_NO_CONTENT
            ]
        ];
    }

    /**
     * @param $propertyId
     * @param $format
     * @param $result
     * @param int $statusCode
     *
     * @test
     * @dataProvider dataProviderGetUnits
     */
    public function getUnits($propertyId, $format, $result, $statusCode = Response::HTTP_OK)
    {
        $client = $this->getClient();

        $client->request(
            'GET',
            "/api/properties/{$propertyId}/units.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::ACCESS_TOKEN,
            ]
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $data = $this->parseContent($client->getResponse()->getContent(), $format);

        if ($data) {
            $this->assertEquals($data['status'], $result['status']);
            $this->assertEquals($data['units'][0], $result['units'][0]);
        }
    }

    public static function dataProviderCreateProperty()
    {
        return [
            [
                [
                    'address' => '950 Andante Rd, Santa Barbara, CA 93105',
                    'is_single' => true,
                ],
                'json',
                [
                    'status' => 'OK',
                    'property_id' => '20',
                ],
            ],
            [
                [
                    'address' => '949 Andante Rd, Santa Barbara, CA 93105',
                    'is_single' => false,
                ],
                'json',
                [
                    'status' => 'Error',
                    'status_code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Unit is required for not standalone property'
                ],
                Response::HTTP_BAD_REQUEST
            ],
            [
                [
                    'address' => '950 Andante Rd, Santa Barbara, CA 93105',
                    'is_single' => false,
                    'unit_name' => '1-a',
                ],
                'json',
                [
                    'status' => 'Error',
                    'status_code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'You can\'t change property "IS_SINGLE"'
                ],
                Response::HTTP_BAD_REQUEST
            ],
            [
                [
                    'address' => '948 Andante Rd, Santa Barbara, CA 93105',
                    'is_single' => false,
                    'unit_name' => '1-a',
                ],
                'json',
                [
                    'status' => 'OK',
                    'property_id' => 21,
                    'unit_id' => 32
                ],
            ],
            [
                [
                    'address' => '948 Andante Rd, Santa Barbara, CA 93105',
                    'is_single' => false,
                    'unit_name' => '2-a',
                ],
                'json',
                [
                    'status' => 'OK',
                    'property_id' => 21,
                    'unit_id' => 33
                ],
            ],
        ];
    }
    /**
     * @param $params
     * @param $format
     * @param $result
     * @param int $statusCode
     *
     * @test
     * @dataProvider dataProviderCreateProperty
     */
    public function createProperty($params, $format, $result, $statusCode = Response::HTTP_OK)
    {
        $client = $this->getClient();

        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('jms_serializer');

        $client->request(
            'POST',
            "/api/properties/create.{$format}",
            [],
            [],
            [
                'CONTENT_TYPE' => static::$formats[$format][0],
                'HTTP_AUTHORIZATION' => 'Bearer ' . static::ACCESS_TOKEN,
            ],
            $serializer->serialize($params, $format)
        );

        $this->assertResponse($client->getResponse(), $statusCode, $format);

        $this->assertResponseContent($client->getResponse()->getContent(), $result, $format);
    }
}
