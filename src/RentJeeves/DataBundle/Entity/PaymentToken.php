<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token as BasePaymentToken;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentTokenRepository")
 * @ORM\Table(name="rj_payment_token")
 */
class PaymentToken extends BasePaymentToken
{
}
