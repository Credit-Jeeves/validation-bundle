<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class JobRelatedCreditTrack extends JobRelatedEntities
{
    /**
     * @ORM\ManyToOne(
     *      targetEntity = "\RentJeeves\DataBundle\Entity\PaymentAccount",
     *      inversedBy = "creditTrackJobs",
     *      fetch = "EAGER"
     * )
     * @ORM\JoinColumn(name="credit_track_payment_account_id", referencedColumnName="id", nullable=true)
     */
    protected $creditTrackPaymentAccount;

    /**
     * @param PaymentAccount $creditTrackPaymentAccount
     *
     * @return $this
     */
    public function setCreditTrackPaymentAccount(PaymentAccount $creditTrackPaymentAccount)
    {
        $this->creditTrackPaymentAccount = $creditTrackPaymentAccount;
        return $this;
    }

    /**
     * @return PaymentAccount
     */
    public function getCreditTrackPaymentAccount()
    {
        return $this->creditTrackPaymentAccount;
    }
}
