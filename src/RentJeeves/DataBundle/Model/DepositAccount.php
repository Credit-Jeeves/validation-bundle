<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
abstract class DepositAccount
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="deposit_accounts"
     * )
     * @ORM\JoinColumn(
     *     name="group_id",
     *     referencedColumnName="id"
     * )
     * @var \CreditJeeves\DataBundle\Entity\Group
     */
    protected $group;

    /**
     * @ORM\Column(
     *     name="merchant_name",
     *     type="string",
     *     length=255
     * )
     */
    protected $merchantName;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set processorToken
     *
     * @param string $merchantName
     * @return DepositAccount
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;

        return $this;
    }

    /**
     * Get processorToken
     *
     * @return string
     */
    public function getMerchantName()
    {
        return $this->merchantName;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Landlord
     */
    public function getGroup()
    {
        return $this->group;
    }
}
