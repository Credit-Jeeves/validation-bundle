<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\MappedSuperclass
 */
abstract class UserSettings
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", name="is_base_order_report")
     */
    protected $isBaseOrderReport = false;

    /**
     * @ORM\OneToOne(targetEntity="\CreditJeeves\DataBundle\Entity\User", inversedBy="settings")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\OneToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\PaymentAccount",
     *      inversedBy="creditTrackUserSetting",
     *      cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(
     *      name="credit_track_payment_account_id",
     *      referencedColumnName="id",
     *      nullable=true,
     *      onDelete="SET NULL"
     * )
     *
     * @var PaymentAccount
     */
    protected $creditTrackPaymentAccount = null;

    /**
     * @ORM\Column(type="datetime", name="credit_track_enabled_at", nullable=true)
     */
    protected $creditTrackEnabledAt = null;

    /**
     * @ORM\Column(
     *     type = "date",
     *     name = "scoretrack_free_until ",
     *     nullable = true
     * )
     *
     * @var \DateTime
     */
    protected $scoreTrackFreeUntil;


    /**
     * @return \DateTime
     */
    public function getScoreTrackFreeUntil()
    {
        return $this->scoreTrackFreeUntil;
    }

    /**
     * @param \DateTime $scoreTrackFreeUntil
     */
    public function setScoreTrackFreeUntil(\DateTime $scoreTrackFreeUntil)
    {
        $this->scoreTrackFreeUntil = $scoreTrackFreeUntil;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $isBaseOrderReport
     */
    public function setIsBaseOrderReport($isBaseOrderReport)
    {
        $this->isBaseOrderReport = $isBaseOrderReport;
    }

    /**
     * @return bool
     */
    public function getIsBaseOrderReport()
    {
        return $this->isBaseOrderReport;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param PaymentAccount $paymentAccount
     */
    public function setCreditTrackPaymentAccount($paymentAccount = null)
    {
        $this->creditTrackPaymentAccount = $paymentAccount;
    }

    /**
     * @return PaymentAccount
     */
    public function getCreditTrackPaymentAccount()
    {
        return $this->creditTrackPaymentAccount;
    }

    /**
     * @param DateTime $datetime
     */
    public function setCreditTrackEnabledAt(DateTime $datetime = null)
    {
        $this->creditTrackEnabledAt = $datetime;
    }

    /**
     * @return DateTime
     */
    public function getCreditTrackEnabledAt()
    {
        return $this->creditTrackEnabledAt;
    }
}
