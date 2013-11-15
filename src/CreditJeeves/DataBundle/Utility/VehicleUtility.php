<?php
namespace CreditJeeves\DataBundle\Utility;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("data.utility.vehicle")
 */
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
    private $vehicles = null;

    private static $instance;

    /**
     * @todo depricated
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Static();
        }
        return self::$instance;
    }

    /**
     * @return array
     */
    private function getAmazonData($container)
    {
        if (!$this->vehicles) {
            $this->vehicles = $this->formatAmazonData(json_decode($this->loadAmazonData($container), true));
        }
        
        return $this->vehicles;
        
    }

    /**
     * 
     * @param array $aVehicles
     */
    private function formatAmazonData($aVehicles)
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
    private function loadAmazonData($container = null)
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
    public function getAmazonVehicle($make, $model, $container)
    {
        $aVehicles = $this->getAmazonData($container);
        return isset($aVehicles[$make][$model]) ? $aVehicles[$make][$model] : self::DEFAUL_URL;
    }

    public function getVehicles()
    {
        $aVehicles = self::loadAmazonData();
        return self::formatAmazonData(json_decode($aVehicles, true));
    }
}
