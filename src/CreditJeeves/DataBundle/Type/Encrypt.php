<?php
namespace CreditJeeves\DataBundle\Type;

require_once dirname(dirname(dirname(dirname(__DIR__)))).'/vendor/CreditJeevesSf1/lib/utility/cjEncryptionUtility.class.php';

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class Encrypt extends TextType
{
    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($encValue, AbstractPlatform $platform)
    {
        $value = \cjEncryptionUtility::decrypt(base64_decode($encValue));
        return $value === false ? $encValue : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
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