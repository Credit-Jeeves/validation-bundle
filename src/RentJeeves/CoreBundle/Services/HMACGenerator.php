<?php

namespace RentJeeves\CoreBundle\Services;

use Psr\Log\LoggerInterface;

/**
 * DI\Service('hmac.generator')
 */
class HMACGenerator
{
    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var string
     */
    protected $signParamName;

    /**
     * @var string
     */
    protected $algorithm;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string $secretKey
     * @param string $signParamName
     * @param string $algorithm
     */
    public function __construct(LoggerInterface $logger, $secretKey, $signParamName = 'hmac', $algorithm = 'sha256')
    {
        $this->secretKey = $secretKey;
        $this->signParamName = $signParamName;
        $this->algorithm = $algorithm;
        $this->logger = $logger;
    }

    /**
     * @param array $data
     * @return string
     */
    public function generateHMAC(array $data)
    {
        // signature should created without signature parameter
        if (isset($data[$this->signParamName])) {
            unset($data[$this->signParamName]);
        }

        static::ksortRecursive($data);

        $dataEncoded = $this->serializeArray($data);

        return $this->makeSignature($dataEncoded);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateHMAC(array $data)
    {
        if (empty($data[$this->signParamName])) {
            $this->logger->warning(
                sprintf('No HMAC signature param in data. Expected \'%s\' param', $this->signParamName)
            );
            return false;
        }
        $this->logger->debug(sprintf('Given HMAC Data: %s', json_encode($data)));
        $inputHMAC = $data[$this->signParamName];
        $originHMAC = $this->generateHMAC($data);

        if (strtolower($originHMAC) !== strtolower($inputHMAC)) {
            $this->logger->error(sprintf('Given HMAC: %s != Calculated HMAC: %s', $inputHMAC, $originHMAC));
            return false;
        }

        return true;
    }

    /**
     * @param $array
     * @param int $sortFlags
     * @return bool
     */
    public static function ksortRecursive(&$array, $sortFlags = SORT_REGULAR)
    {
        if (!is_array($array)) {
            return false;
        }
        ksort($array, $sortFlags);
        foreach ($array as &$subArray) {
            static::ksortRecursive($subArray, $sortFlags);
        }

        return true;
    }

    /**
     * @param string $algorithm
     * @throws \InvalidArgumentException
     */
    protected function checkHashAlgorithm($algorithm)
    {
        if (in_array($algorithm = strtolower($algorithm), hash_algos())) {
            $this->algorithm = $algorithm;
        } else {
            throw new \InvalidArgumentException(sprintf('Hash algorithm "%s" is not supported', $algorithm));
        }
    }

    /**
     * @param array $data
     * @return string
     */
    protected function serializeArray(array $data)
    {
        return http_build_query($data);
    }

    /**
     * @param string $dataEncoded
     * @return string
     */
    protected function makeSignature($dataEncoded)
    {
        $this->checkHashAlgorithm($this->algorithm);

        return hash_hmac($this->algorithm, $dataEncoded, $this->secretKey);
    }
}
