<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Holding;

/**
 * @ORM\MappedSuperclass
 */
abstract class ResManSettings
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="bigint"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(
     *      type="encrypt",
     *      name="account_id",
     *      nullable=false
     * )
     * @var integer
     */
    protected $accountId;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="resManSettings",
     *     cascade={"persist", "merge"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="holding_id", referencedColumnName="id", nullable=false, unique=true)
     * @var Holding
     */
    protected $holding;

    /**
     * @return integer
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param integer $accountId
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * @param Holding $holding
     */
    public function setHolding(Holding $holding)
    {
        $this->holding = $holding;
    }

    /**
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
