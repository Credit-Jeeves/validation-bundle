<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class JobRelatedPayment extends JobRelatedEntities
{
    /**
     * @ORM\ManyToOne(targetEntity = "Payment", inversedBy = "jobs", fetch = "EAGER")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=true)
     * @var Payment
     */
    protected $payment;

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
