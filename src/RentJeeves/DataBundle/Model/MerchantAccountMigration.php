<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class MerchantAccountMigration
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(name="heartland_merchant_name", type="string", unique=true)
     *
     * @var string
     */
    protected $heartlandMerchantName;

    /**
     * @ORM\Column(name="aci_division_id", type="string")
     *
     * @var string
     */
    protected $aciDivisionId;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAciDivisionId()
    {
        return $this->aciDivisionId;
    }

    /**
     * @param string $aciDivisionId
     */
    public function setAciDivisionId($aciDivisionId)
    {
        $this->aciDivisionId = $aciDivisionId;
    }

    /**
     * @return string
     */
    public function getHeartlandMerchantName()
    {
        return $this->heartlandMerchantName;
    }

    /**
     * @param string $heartlandMerchantName
     */
    public function setHeartlandMerchantName($heartlandMerchantName)
    {
        $this->heartlandMerchantName = $heartlandMerchantName;
    }
}
