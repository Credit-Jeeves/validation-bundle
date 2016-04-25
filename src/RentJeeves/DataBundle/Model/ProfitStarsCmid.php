<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\Landlord;

/**
 * @ORM\MappedSuperclass
 */
class ProfitStarsCmid
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
     *     targetEntity="RentJeeves\DataBundle\Entity\Landlord",
     *     inversedBy="profitStarsCmid"
     * )
     * @ORM\JoinColumn(name="landlord_id", referencedColumnName="id", nullable=false)
     */
    protected $landlord;

    /**
     * @var string
     *
     * @ORM\Column(name="cmid", type="string", nullable=false)
     */
    protected $cmid;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Landlord
     */
    public function getLandlord()
    {
        return $this->landlord;
    }

    /**
     * @param Landlord $landlord
     */
    public function setLandlord(Landlord $landlord)
    {
        $this->landlord = $landlord;
    }

    /**
     * @return string
     */
    public function getCmid()
    {
        return $this->cmid;
    }

    /**
     * @param string $cmid
     */
    public function setCmid($cmid)
    {
        $this->cmid = $cmid;
    }
}
