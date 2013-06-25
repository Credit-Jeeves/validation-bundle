<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Address as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * Property
 *
 * @ORM\Table(name="cj_property")
 * @ORM\Entity()
 */
class Property extends Base
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
