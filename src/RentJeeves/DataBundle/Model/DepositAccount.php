<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
abstract class DepositAccount extends PaymentAccount
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Landlord",
     *     inversedBy="payment_accounts"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     */
    protected $user;

    /**
     * @ORM\Column(
     *     name="processor_token",
     *     type="string",
     *     length=255
     * )
     */
    protected $processorToken;

    /**
     * Set processorToken
     *
     * @param string $processorToken
     * @return DepositAccount
     */
    public function setProcessorToken($processorToken)
    {
        $this->processorToken = $processorToken;

        return $this;
    }

    /**
     * Get processorToken
     *
     * @return string
     */
    public function getProcessorToken()
    {
        return $this->processorToken;
    }
}
