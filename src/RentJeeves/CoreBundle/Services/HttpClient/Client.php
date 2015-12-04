<?php

namespace RentJeeves\CoreBundle\Services\HttpClient;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Message\RequestInterface as Request;
use Guzzle\Http\Message\Response;
use Psr\Log\LoggerInterface as Logger;

/**
 * DI\Service("http_client")
 */
class Client implements ClientInterface
{
    const LOG_PREFIX = '[HTTP CLIENT]';
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var GuzzleClient
     */
    protected $guzzleClient;

    /**
     * Number of retries
     * @var int
     */
    protected $retries = 0;

    /**
     * @param GuzzleClient $guzzleClient
     * @param Logger $logger
     */
    public function __construct(GuzzleClient $guzzleClient, Logger $logger)
    {
        $this->logger = $logger;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * {@inheritdoc}
     */
    public function send($method, $uri, $headers = null, $body = null, array $options = [])
    {
        empty($options) || $this->setConfig($options);
        $request = $this->guzzleClient->createRequest($method, $uri, $headers, $body);

        $this->logger->debug(
            sprintf(
                static::LOG_PREFIX . 'Send request %s',
                $request
            ),
            $options
        );

        $response = $this->sendRequest($request);

        $this->logger->debug(
            sprintf(
                static::LOG_PREFIX . 'Retrieved response %s',
                $response
            ),
            $options
        );

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($url)
    {
        $this->guzzleClient->setBaseUrl($url);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $options)
    {
        $options = $this->resolveConfigOptions($options);
        $this->guzzleClient->setConfig($options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNumberRetries($retries)
    {
        $this->retries = $retries;

        return $this;
    }

    /**
     * @param Request $request
     * @param int $retried
     * @return array|Response
     * @throws \Exception
     */
    protected function sendRequest(Request $request, $retried = 0)
    {
        try {
            return $this->guzzleClient->send($request);
        } catch (CurlException $e) {
            if (($this->retries > 0) &&
                ($e->getErrorNo() === CURLE_OPERATION_TIMEOUTED || $e->getErrorNo() === CURLE_COULDNT_CONNECT)
            ) {
                if ($retried < $this->retries) {
                    $this->logger->debug(
                        sprintf(static::LOG_PREFIX . 'Retrying number %d to send request', ++$retried)
                    );

                    return $this->sendRequest($request, $retried);
                }
                $this->logger->alert(
                    sprintf(
                        static::LOG_PREFIX . 'Retrying numbers is over to send request %s',
                        $request
                    )
                );
            }
            throw $e;
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    static::LOG_PREFIX . 'Error message: %s In file: %s By line: %s',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );

            throw $e;
        }
    }

    /**
     * @param array $options
     * @return  array
     */
    protected function resolveConfigOptions(array $options)
    {
        if (isset($options['timeout'])) {
            $options['curl.options'][CURLOPT_TIMEOUT] = $options['timeout'];
            unset($options['timeout']);
        }

        if (isset($options['connect_timeout'])) {
            $options['curl.options'][CURLOPT_CONNECTTIMEOUT] = $options['connect_timeout'];
            unset($options['connect_timeout']);
        }

        return $options;
    }
}
