<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Model\Payment as Base;

/**
 * @ORM\Table(name="rj_payment")
 * @ORM\Entity
 */
class Payment extends Base
{
}
