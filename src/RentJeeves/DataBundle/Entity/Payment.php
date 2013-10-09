<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Payment as Base;

/**
 * @ORM\Table(name="rj_payment")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentRepository")
 */
class Payment extends Base
{
    public function checkContract()
    {
        $contract = $this->getContract()->initiatePaidTo();
        $this->setContract($contract);
        return $this;
    }
}
