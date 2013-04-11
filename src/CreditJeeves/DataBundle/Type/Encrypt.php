<?php
namespace CreditJeeves\DataBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class Encrypt extends TextType
{
    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($encValue, AbstractPlatform $platform)
    {
        require_once __DIR__ . '/../../../../vendor/CreditJeevesSf1/lib/utility/cjEncryptionUtility.class.php';
        $value = \cjEncryptionUtility::decrypt(base64_decode($encValue));
        return $value === false ? $encValue : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        require_once __DIR__ . '/../../../../vendor/CreditJeevesSf1/lib/utility/cjEncryptionUtility.class.php';
        return base64_encode(\cjEncryptionUtility::encrypt($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return strtolower(get_class($this));
    }
}
