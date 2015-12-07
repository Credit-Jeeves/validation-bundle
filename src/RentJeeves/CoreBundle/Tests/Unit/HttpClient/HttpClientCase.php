<?php

namespace RentJeeves\CoreBundle\Tests\Unit\HttpClient;

use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Message\Request;
use RentJeeves\CoreBundle\HttpClient\HttpClient;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class HttpClientCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @return array
     */
    public function shouldRetriedDataProvider()
    {
        return [
            [2],
            [0],
        ];
    }

    /**
     * @param $numberRetries
     *
     * @test
     * @expectedException \Guzzle\Http\Exception\CurlException
     * @dataProvider shouldRetriedDataProvider
     */
    public function shouldRetriedIfTimeout($numberRetries)
    {
        $guzzleClient = $this->getMock('Guzzle\Http\Client');

        $request = new Request('POST', '/');
        $guzzleClient
            ->expects($this->once())
            ->method('createRequest')
            ->will($this->returnValue($request));

        // send method should be called number of retries + first time

        $curlException = new CurlException();
        $curlException->setError('', CURLE_OPERATION_TIMEOUTED);
        $guzzleClient
            ->expects($this->exactly($numberRetries + 1))
            ->method('send')
            ->will($this->throwException($curlException));

        $client = new HttpClient($guzzleClient, $this->getLoggerMock());

        $client->setNumberRetries($numberRetries);

        $client->send('POST', '/');
    }

    /**
     * @param int $numberRetries
     *
     * @test
     * @expectedException \Guzzle\Http\Exception\CurlException
     * @dataProvider shouldRetriedDataProvider
     */
    public function shouldRetriedIfConnectionTimeout($numberRetries)
    {
        $guzzleClient = $this->getMock('Guzzle\Http\Client');

        $request = new Request('POST', '/');
        $guzzleClient
            ->expects($this->once())
            ->method('createRequest')
            ->will($this->returnValue($request));

        // send method should be called number of retries + first time

        $curlException = new CurlException();
        $curlException->setError('', CURLE_COULDNT_CONNECT);
        $guzzleClient
            ->expects($this->exactly($numberRetries + 1))
            ->method('send')
            ->will($this->throwException($curlException));

        $client = new HttpClient($guzzleClient, $this->getLoggerMock());

        $client->setNumberRetries($numberRetries);

        $client->send('POST', '/');
    }
}
