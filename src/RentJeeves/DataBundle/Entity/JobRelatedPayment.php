<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class JobRelatedPayment extends JobRelatedEntities
{
    /**
     * @param Payment $payment
     *
     * @return $this
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
