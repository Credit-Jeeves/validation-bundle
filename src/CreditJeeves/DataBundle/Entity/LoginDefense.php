<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\LoginDefense as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cj_login_defense")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\LoginDefenseRepository")
 */
class LoginDefense extends Base
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
