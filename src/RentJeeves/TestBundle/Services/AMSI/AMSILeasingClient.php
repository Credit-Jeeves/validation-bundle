<?php

namespace RentJeeves\TestBundle\Services\AMSI;

use RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILeasingClient as Base;
use Symfony\Component\Config\FileLocator;

class AMSILeasingClient extends Base
{
    protected $mapping = [
        'GetPropertyResidents' => 'AMSI-GetPropertyResidents.xml',
        'GetPropertyUnits' => 'AMSI-GetPropertyUnits.xml'
    ];

    /**
     * @param $function
     * @param array $params
     *
     * @return string
     */
    public function sendRequest($function, array $params)
    {
        if (!isset($this->mapping[$function])) {
            return parent::sendRequest($function, $params);
        }

        $locator = new FileLocator([__DIR__.'/../../Resources/fixtures']);
        $filePath = $locator->locate($this->mapping[$function], null, true);

        if (!file_exists($filePath)) {
            throw new \LogicException(sprintf('Can\'t find file for function % by path %', $function, $filePath));
        }

        return file_get_contents($filePath);
    }
}
