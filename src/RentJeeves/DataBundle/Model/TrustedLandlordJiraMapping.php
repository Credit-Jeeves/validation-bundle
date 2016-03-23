<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class TrustedLandlordJiraMapping
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
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\TrustedLandlord",
     *     inversedBy="jiraMapping"
     * )
     * @ORM\JoinColumn(
     *     name="trusted_landlord_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected $trustedLandlord;

    /**
     * @var string
     *
     * @ORM\Column(name="jira_key", type="string", length=255)
     */
    protected $jiraKey;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $jiraKey
     */
    public function setJiraKey($jiraKey)
    {
        $this->jiraKey = $jiraKey;
    }

    /**
     * @return string
     */
    public function getJiraKey()
    {
        return $this->jiraKey;
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     */
    public function setTrustedLandlord(TrustedLandlord $trustedLandlord)
    {
        $this->trustedLandlord = $trustedLandlord;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\TrustedLandlord
     */
    public function getTrustedLandlord()
    {
        return $this->trustedLandlord;
    }
}
