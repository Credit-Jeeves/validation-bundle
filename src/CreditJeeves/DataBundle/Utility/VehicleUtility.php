<?php
namespace CreditJeeves\DataBundle\Utility;

class VehicleUtility
{
    /**
     * 
     * @var string
     */
    const FILE_NAME = 'vehicles.json';

    /**
     * 
     * @var string
     */
    const DEFAUL_URL = 'https://carimg.s3.amazonaws.com/8354_st0640_037.jpg';

    /**
     * 
     * @var array
     */
    private static $VEHICLES = null;

    /**
     * @return array
     */
    private static function getAmazonData($container)
    {
        if (!self::$VEHICLES) {
            self::$VEHICLES = self::formatAmazonData(json_decode(self::loadAmazonData($container), true));
        }
        
        return self::$VEHICLES;
        
    }

    /**
     * 
     * @param array $aVehicles
     */
    private static function formatAmazonData($aVehicles)
    {
        $aResult = array();
        foreach ($aVehicles as $make => $aModels) {
            foreach ($aModels['models'] as $aModel) {
                $aResult[$make][$aModel['name']] = $aModel['url'];
            }
        }
        return $aResult;
    }

    /**
     * 
     */
    private static function loadAmazonData($container = null)
    {
        $filename = __DIR__ . '/../Resources/public/'.self::FILE_NAME;
        return file_get_contents($filename);
    }

    /**
     * 
     * @param string $make
     * @param string $model
     * @return string
     */
    public static function getAmazonVehicle($make, $model, $container)
    {
        $aVehicles = self::getAmazonData($container);
        return isset($aVehicles[$make][$model]) ? $aVehicles[$make][$model] : self::DEFAUL_URL;
    }

    public static function getVehicles()
    {
        $aVehicles = self::loadAmazonData();
        return self::formatAmazonData(json_decode($aVehicles, true));
    }
}
