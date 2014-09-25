<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("CurrentAddress")
 */
class CurrentAddress extends Address
{
}
