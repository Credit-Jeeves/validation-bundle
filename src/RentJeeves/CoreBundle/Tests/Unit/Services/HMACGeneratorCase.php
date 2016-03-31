<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use RentJeeves\CoreBundle\Services\HMACGenerator;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class HMACGeneratorCase extends UnitTestBase
{
    const SECRET_KEY = 'testSecretKey';

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Hash algorithm "bla-bla-bla" is not supported
     */
    public function shouldThrowExceptionOnInvalidHashAlgorithm()
    {
        $generator = new HMACGenerator(self::SECRET_KEY, 'hmac', 'bla-bla-bla');

        $generator->validateHMAC(['hmac' => 'invalidHMAC', 'data' => 'hmmm']);
    }

    /**
     * @return array
     */
    public function sortArrayDataProvider()
    {
        return [
            [
                [
                    1 => 'test1',
                    0 => 'test0',
                    2 => 'test2',
                ],
                ['test0', 'test1', 'test2'],
            ],
            [
                [
                    'test1' => 1,
                    'test0' => 0,
                    'test2' => 2,
                ],
                [
                    'test0' => 0,
                    'test1' => 1,
                    'test2' => 2,
                ],
            ],
            [
                [
                    'test1' => 1,
                    'test3' => [
                        'test1' => 1,
                        'test3' => [
                            'test1' => 1,
                            'test0' => 0,
                            'test2' => 2,
                        ],
                        'test0' => 0,
                        'test2' => 2,
                    ],
                    'test0' => 0,
                    'test2' => 2,
                ],
                [
                    'test0' => 0,
                    'test1' => 1,
                    'test2' => 2,
                    'test3' => [
                        'test0' => 0,
                        'test1' => 1,
                        'test2' => 2,
                        'test3' => [
                            'test0' => 0,
                            'test1' => 1,
                            'test2' => 2,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $inputArray
     * @param array $resultArray
     *
     * @test
     * @dataProvider sortArrayDataProvider
     */
    public function shouldSortArrayByKeys($inputArray, $resultArray)
    {
        HMACGenerator::ksortRecursive($inputArray);
        $this->assertEquals($resultArray, $inputArray, 'Array sorting is invalid');
    }

    /**
     * @return array
     */
    public function creationSignatureDataProvider()
    {
        return [
            [
                [
                    'b' => 2,
                    'c' => 3,
                    'hmac' => 'cd1f75fec0ae0df4473f64b0899ae7798ea8a0cdc01b2973e78bb2a67667ee8c',
                    'd' => 4,
                    'a' => '1',
                ],
                'cd1f75fec0ae0df4473f64b0899ae7798ea8a0cdc01b2973e78bb2a67667ee8c'
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                    'd' => 4,
                    'hmac' => 'cd1f75fec0ae0df4473f64b0899ae7798ea8a0cdc01b2973e78bb2a67667ee8c',
                ],
                'cd1f75fec0ae0df4473f64b0899ae7798ea8a0cdc01b2973e78bb2a67667ee8c'
            ],
            [
                [
                    'testb' => 'test2',
                    'testc' => 'test3',
                    'testd' => 'test4',
                    'testa' => 'test1',
                    'hmac' => 'd51fd91b09c95496ecc352e52eb4183efd899869f5736c99e5d42fde57a3fae2',

                ],
                'd51fd91b09c95496ecc352e52eb4183efd899869f5736c99e5d42fde57a3fae2'
            ],
        ];
    }

    /**
     * @param array $data
     * @param string $resultSignature
     *
     * @test
     * @dataProvider creationSignatureDataProvider
     */
    public function shouldCreateRightSignature($data, $resultSignature)
    {
        $generator = new HMACGenerator(self::SECRET_KEY);

        $this->assertEquals($resultSignature, $generator->generateHMAC($data), 'Invalid creation signature');
    }

    /**
     * @param array $data
     *
     * @test
     * @dataProvider creationSignatureDataProvider
     */
    public function shouldBeValidSignature($data)
    {
        $generator = new HMACGenerator(self::SECRET_KEY);

        $this->assertTrue($generator->validateHMAC($data), 'Signature should be valid');
    }

    /**
     * @return array
     */
    public function invalidSignatureDataProvider()
    {
        return [
            [[]],
            [
                [
                    'a' => '1',
                    'b' => 2,
                    'c' => 3,
                    'hmac' => 'invalid_hmac',
                    'd' => 4,
                ]
            ],
            [
                [
                    'a' => '1',
                    'b' => 2,
                    'c' => 3,
                    'd' => 4,
                ]
            ],
        ];
    }

    /**
     * @param array $data
     *
     * @test
     * @dataProvider invalidSignatureDataProvider
     */
    public function shouldBeInValidSignature($data)
    {
        $generator = new HMACGenerator(self::SECRET_KEY);

        $this->assertFalse($generator->validateHMAC($data), 'Signature should be invalid');
    }
}
