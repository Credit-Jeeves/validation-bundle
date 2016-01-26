<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class ProfitStarsSettings
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \CreditJeeves\DataBundle\Entity\Holding
     *
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="profitStarsSettings",
     *     cascade={"persist", "remove", "merge"}
     * )
     * @ORM\JoinColumn(name="holding_id", referencedColumnName="id", nullable=false)
     */
    protected $holding;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_id", type="string", nullable=false)
     */
    protected $merchantId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\Holding $holding
     */
    public function setHolding(\CreditJeeves\DataBundle\Entity\Holding $holding)
    {
        $this->holding = $holding;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }
}
