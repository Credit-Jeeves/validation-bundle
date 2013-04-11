<?php
namespace CreditJeeves\DataBundle\Enum;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Base Enum class
 *
 * @author Ton Sharp <66ton99@gmail.com>
 */
abstract class Base extends Type
{
    /**
     * Titles cache
     *
     * @var array
     */
    private static $cachedTitles = array();

    /**
     * Specific titles
     *
     * @var array
     */
    protected static $titles = array();

    /**
     * Pointer to the translator function
     *
     * @var callback
     */
    protected static $translator;

    /**
     * @return array
     */
    public static function all()
    {
        return static::getRC()->getConstants();
    }

    /**
     * Values
     *
     * @return array
     */
    public static function values()
    {
        return array_values(static::all());
    }

    /**
     * Keys
     *
     * @return array
     */
    public static function keys()
    {
        return array_keys(static::all());
    }

    /**
     * Translated titles
     *
     * @return array
     */
    public static function cachedTitles($sort = false, $isTitlesAsKeys = false)
    {
        $class = get_called_class();
        if (!empty(self::$cachedTitles[$class]) && null !== self::$cachedTitles[$class]) {
            return self::$cachedTitles[$class];
        }
        $titles = array();
        foreach (static::values() as $val) {
            if (!empty(static::$titles[$val])) {
                $title = static::$titles[$val];
            } else {
                $title = ucfirst(str_replace('_', ' ', $val));
            }
            if ($isTitlesAsKeys) {
                $titles[$title] = $title;
            } else {
                $titles[$val] = $title;
            }
        }
        $titles = array_map(self::getTranslator(), $titles);
        if ($sort) {
            asort($titles);
        }

        return self::$cachedTitles[$class] = $titles;
    }

    /**
     * Translated title
     *
     * @var string|int $value - on '' & null will return default value
     *
     * @return string
     */
    public static function title($value)
    {
        $titles = static::cachedTitles();
        if ('' === $value || null === $value) {
            $value = static::defautVal();
        }
        static::throwsInvalid($value);

        return $titles[$value];
    }

    /**
     * Check value
     *
     * @param string|int $value
     *
     * @return bool
     */
    public static function isValid($value)
    {
        return in_array($value, static::all(), false);
    }

    /**
     * Check value with exception on fail
     *
     * @param mixed $value
     *
     * @throws \InvalidArgumentException if the value does not valid for the enum type
     *
     * @return void
     */
    public static function throwsInvalid($value)
    {
        if (!static::isValid($value)) {
            throw new \InvalidArgumentException(
                printf(
                    'The enum type `%s` does not contains value `%s` . Possible values are `%s`',
                    get_called_class(),
                    var_export($value, true),
                    static::implode('`, `')
                )
            );
        }
    }

    /**
     * Implode all values to the string separated by $separator
     *
     * @param string $separator
     *
     * @return string
     */
    public static function implode($separator = ', ')
    {
        return implode($separator, static::values());
    }

    /**
     * Set translator callback function
     *
     * @param callback $translator
     *
     * @return void
     */
    public static function setTranslator($translator)
    {
        self::$translator = $translator;
    }

    /**
     * Get translator function
     *
     * @return callback
     */
    public static function getTranslator()
    {
        if (false == self::$translator) {
            self::$translator = function ($title) {
                return $title;
            };
        }

        return self::$translator;
    }

    /**
     * Get reflection class
     *
     * @return \ReflectionClass
     */
    protected static function getRC()
    {
        return new \ReflectionClass(get_called_class());
    }

    /**
     * Search in titles
     *
     * @param string $needle
     *
     * @return array - of keys
     */
    public static function search($needle)
    {
        $return = array();
        foreach (static::cachedTitles() as $key => $val) {
            if (false !== stristr($val, $needle)) {
                $return[] = $key;
            }
        }

        return $return;
    }

    /**
     * Get default values by default it is first element
     *
     * @return string
     */
    public static function defaultValue()
    {
        $values = (static::values());
        return array_shift($values);
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM(" . static::implode() . ")COMMENT '(DC2Type:" . $this->getName() . ")'";
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        static::throwsInvalid($value);
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return get_called_class();
    }
}
