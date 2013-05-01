<?php
namespace CreditJeeves\DataBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class EncryptObject extends Encrypt
{
    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($encValue, AbstractPlatform $platform)
    {
        return unserialize(parent::convertToPHPValue($encValue, $platform));
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return parent::convertToDatabaseValue(serialize($value), $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return strtolower(get_class($this));
    }
}
