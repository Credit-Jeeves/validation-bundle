<?php
/**
 * @deprecated
 */
class sfConfig
{
    protected static $configs = array(
        'experian_net_connect_XML_root' => array()
    );

    /**
     * @deprecated
     *
     * @param $name
     * @param null $default
     */
    public static function get($name, $default = null)
    {
        return isset(self::$configs[$name])?self::$configs[$name]:$default;
    }
}
