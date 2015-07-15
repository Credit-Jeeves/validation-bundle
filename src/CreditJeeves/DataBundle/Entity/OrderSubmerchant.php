<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\GenericSerializationVisitor;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;

/**
 * @ORM\Entity
 */
class OrderSubmerchant extends Order
{
    /**
     * {@inheritdoc}
     */
    public function getObjectType()
    {
        return OrderAlgorithmType::SUBMERCHANT;
    }

    /**
     * @Serializer\Groups({"payment"})
     * @Serializer\HandlerCallback("json", direction = "serialization")
     *
     * @param GenericSerializationVisitor $visitor
     * @return array
     */
    public function getItem(GenericSerializationVisitor $visitor = null)
    {
        return parent::getItem($visitor);
    }
}
