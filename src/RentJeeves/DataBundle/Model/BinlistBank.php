<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class BinlistBank
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="bank_name", unique=true, type="string", length=255)
     *
     * @var string
     */
    protected $bankName;

    /**
     * @ORM\Column(
     *     name="low_debit_fee",
     *     type="boolean",
     *     nullable=false,
     *     options={
     *         "default"="0"
     *     }
     * )
     *
     * @var boolean
     */
    protected $lowDebitFee = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\DebitCardBinlist",
     *     mappedBy="binlistBank",
     *     cascade={"all"}
     * )
     *
     * @var ArrayCollection|DebitCardBinlist[]
     */
    protected $debitCards;

    public function __construct()
    {
        $this->debitCards = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function getLowDebitFee()
    {
        return $this->lowDebitFee;
    }

    /**
     * @param boolean $lowDebitFee
     */
    public function setLowDebitFee($lowDebitFee)
    {
        $this->lowDebitFee = $lowDebitFee;
    }
}
