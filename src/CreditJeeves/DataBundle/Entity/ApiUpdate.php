<?php

namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\ApiUpdate as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="api_update_user")
 * @ORM\Entity
 */
class ApiUpdate extends Base
{
}
