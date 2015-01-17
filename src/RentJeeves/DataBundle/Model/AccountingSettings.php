<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Holding;

/**
 * @ORM\MappedSuperclass
 */
abstract class AccountingSettings
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
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="accountingSettings",
     *     cascade={"persist", "merge"}
     * )
     * @ORM\JoinColumn(name="holding_id", referencedColumnName="id", nullable=false, unique=true)
     * @var Holding
     */
    protected $holding;

    /**
     * @ORM\Column(
     *     type="ApiIntegrationType",
     *     name="api_integration"
     * )
     * @var string
     */
    protected $apiIntegration;

    /**
     * @return string
     */
    public function getApiIntegration()
    {
        return $this->apiIntegration;
    }

    /**
     * @param string $apiIntegration
     */
    public function setApiIntegration($apiIntegration)
    {
        $this->apiIntegration = $apiIntegration;
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
