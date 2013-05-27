<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\CheckoutAuthorizeNetAim as Base;

/**
 * CheckoutAuthorizeNetAim
 *
 * @ORM\Table(name="cj_checkout_authorize_net_aim")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAimRepository")
 */
class CheckoutAuthorizeNetAim extends Base
{
}
