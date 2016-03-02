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
    protected $algorithm = 'sha256';

    /**
     * @var string
     */
    protected $signParamName = 'hmac';

    /**
     * @param string $secretKey
     * @param string $algorithm
     * @param string $signParamName
     */
    public function __construct($secretKey, $algorithm = 'sha256', $signParamName = 'hmac')
    {
        $this->secretKey = $secretKey;
        $this->signParamName = $signParamName;
        $this->setHashAlgorithm($algorithm);
    }

    /**
     * @param string $algorithm
     * @throws \RuntimeException
     */
    public function setHashAlgorithm($algorithm)
    {
        if (in_array($algorithm = strtolower($algorithm), hash_algos())) {
            $this->algorithm = $algorithm;
        } else {
            throw new \RuntimeException(sprintf('Hash algorithm "%s" is not supported', $algorithm));
        }
    }

    /**
     * @param $signParamName
     */
    public function setSignParamName($signParamName)
    {
        $this->signParamName = $signParamName;
    }

    /**
     * @param array $data
     * @param string $secretKey
     * @return string
     */
    public function makeDataHMAC(array $data, $secretKey = null)
    {
        $secretKey = $secretKey ?: $this->secretKey;

        if(isset($data[$this->signParamName])) {
            unset($data[$this->signParamName]);
        }

        static::ksortRecursive($data);

        $dataEncoded = $this->serializeArray($data);

        return $this->makeSignature($dataEncoded, $secretKey);
    }

    /**
     * @param array $data
     * @param string $secretKey
     * @param string $signParamName
     * @return bool
     */
    public function checkDataHMAC(array $data, $secretKey = null, $signParamName = null){

        $secretKey = $secretKey ?: $this->secretKey;

        $signParamName = $signParamName ?: $this->signParamName;

        if (empty($data[$signParamName])) {
            return false;
        }

        $HMAC = $data[$signParamName];
        unset($data[$signParamName]);

        $originHMAC = $this->makeDataHMAC($data, $secretKey);

        if  (strtolower($originHMAC) === strtolower($HMAC)) {
            return true;
        }

        return false;
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
     * @param string $secretKey
     * @return string
     */
    protected function makeSignature($dataEncoded, $secretKey)
    {
        return hash_hmac($this->algorithm, $dataEncoded, $secretKey);
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
}
