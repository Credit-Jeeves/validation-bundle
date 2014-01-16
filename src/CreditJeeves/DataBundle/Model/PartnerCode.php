<?php

namespace CreditJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\MappedSuperclass
 */
abstract class PartnerCode
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Partner"
     * )
     */
    protected $partner;

    /**
     * @ORM\OneToOne(targetEntity="\CreditJeeves\DataBundle\Entity\User", inversedBy="partnerCode")
     */
    protected $user;

    /**
     * @ORM\Column(
     *     type="string",
     *     nullable=true
     * )
     */
    protected $code;

    /**
     * @ORM\Column(
     *     name="payment_date",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $firstPaymentDate;

    /**
     * @ORM\Column(
     *     name="is_charged",
     *     type="boolean",
     *     options={
     *         "default"="0"
     *     }
     * )
     */
    protected $isCharged = false;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param Partner $partner
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
    }

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
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
     * @param DateTime $firstPaymentDate
     */
    public function setFirstPaymentDate(DateTime $firstPaymentDate)
    {
        $this->firstPaymentDate = $firstPaymentDate;
    }

    /**
     * @return DateTime
     */
    public function getFirstPaymentDate()
    {
        return $this->firstPaymentDate;
    }

    /**
     * @param boolean $isCharged
     */
    public function setIsCharged($isCharged)
    {
        $this->isCharged = $isCharged;
    }

    /**
     * @return boolean
     */
    public function getIsCharged()
    {
        return $this->isCharged;
    }
}
