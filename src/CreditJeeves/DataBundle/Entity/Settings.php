<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Settings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cj_settings")
 */
class Settings extends Base
{
}
