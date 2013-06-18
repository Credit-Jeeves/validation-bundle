<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Landlord extends User
{
    /**
     * @var string
     */
    protected $type = UserType::LANDLORD;
}
