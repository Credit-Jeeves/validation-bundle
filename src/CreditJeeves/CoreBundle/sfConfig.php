<?php

use Symfony\Component\Yaml\Yaml;

/**
 * @deprecated
 */
class sfConfig
{
    private static $init = false;

    private static $config = array();

    private static function getRoot()
    {
        return realpath(__DIR__ . '/../../..');
    }

    private static function replacePlaceHolders($value)
    {
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = self::replacePlaceHolders($val);
            }
        } elseif (!is_bool($value)) {
            foreach (self::$config as $key => $val) {
                if (is_string($val)) {
                    $key = strtoupper($key);
                    $value = str_replace("%{$key}%", $val, $value);
                }
            }
        }
        return $value;
    }

    /**
     * @deprecated
     * @param array $configs
     * @param $key
     */
    public static function fill(array $configs, $key)
    {
        foreach ($configs as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $subName => $subValue) {
                    self::$config["{$key}_{$name}_{$subName}"] = self::replacePlaceHolders($subValue);
                }
            } else {
                self::$config["{$key}_{$name}"] = self::replacePlaceHolders($value);
            }
        }
    }

    private static function init()
    {
        if (self::$init) return;

        self::$config['sf_data_dir'] = self::getRoot() . '/vendor/CreditJeevesSf1/data';
        self::$config['sf_log_dir'] = self::getRoot() . '/app/logs';
        self::$config['sf_upload_dir'] = self::getRoot() . '/web/uploads';

        $configs = Yaml::parse(self::getRoot() . '/vendor/CreditJeevesSf1/config/experian.yml');
        self::fill($configs['all'], 'experian');
        self::$init = true;
    }

    /**
     * @deprecated
     *
     * @param $name
     * @param null $default
     */
    public static function get($name, $default = null)
    {
        self::init();
        return isset(self::$config[$name])?self::$config[$name]:$default;
    }

    /**
     * Indicates whether or not a config parameter exists.
     *
     * @deprecated
     * @param string $name A config parameter name
     *
     * @return bool true, if the config parameter exists, otherwise false
     */
    public static function has($name)
    {
        self::init();
        return array_key_exists($name, self::$config);
    }

    /**
     * Sets a config parameter.
     *
     * If a config parameter with the name already exists the value will be overridden.
     *
     * @deprecated
     * @param string $name  A config parameter name
     * @param mixed  $value A config parameter value
     */
    public static function set($name, $value)
    {
        self::init();
        self::$config[$name] = $value;
    }

    /**
     * Sets an array of config parameters.
     *
     * If an existing config parameter name matches any of the keys in the supplied
     * array, the associated value will be overridden.
     *
     * @deprecated
     * @param array $parameters An associative array of config parameters and their associated values
     */
    public static function add($parameters = array())
    {
        self::init();
        self::$config = array_merge(self::$config, $parameters);
    }

    /**
     * Retrieves all configuration parameters.
     *
     * @deprecated
     * @return array An associative array of configuration parameters.
     */
    public static function getAll()
    {
        self::init();
        return self::$config;
    }

    /**
     * @deprecated
     * Clears all current config parameters.
     */
    public static function clear()
    {
        self::$config = array();
    }
}
