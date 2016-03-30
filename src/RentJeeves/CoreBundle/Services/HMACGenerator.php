<?php

namespace RentJeeves\CoreBundle\Services;

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
     * @param string $secretKey
     * @param string $signParamName
     * @param string $algorithm
     */
    public function __construct($secretKey, $signParamName = 'hmac', $algorithm = 'sha256')
    {
        $this->secretKey = $secretKey;
        $this->signParamName = $signParamName;
        $this->algorithm = $algorithm;
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
            return false;
        }

        $inputHMAC = $data[$this->signParamName];
        $originHMAC = $this->generateHMAC($data);

        if (strtolower($originHMAC) === strtolower($inputHMAC)) {
            return true;
        }

        return false;
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
