<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Model\GroupSettings as Base;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Exception\LogicException;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GroupSettings
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\GroupSettingsRepository")
 * @ORM\Table(name="rj_group_settings")
 * @ORM\HasLifecycleCallbacks
 */
class GroupSettings extends Base
{
    /**
     * @TODO need test for this functional.
     * @ORM\PreUpdate()
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $changeSet = $args->getEntityChangeSet();

        if (!isset($changeSet['isIntegrated']) ||
            !isset($changeSet['isIntegrated'][0]) ||
            !isset($changeSet['isIntegrated'][1])
        ) {
            return;
        }

        $isIntegratedBefore = $changeSet['isIntegrated'][0];
        $isIntegratedNew = $changeSet['isIntegrated'][1];

        /**
         * Once a client is set up as integrated, do not allow to turn off afterwards.
         * https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
         */
        if ($isIntegratedBefore && !$isIntegratedNew) {
            throw new LogicException("Once a client is set up as integrated, we not allow to turn off afterwards.");
        }
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("dueDays")
     * @Serializer\Groups({"payRent"})
     *
     * @return array
     */
    public function getDueDays()
    {
        if ($this->getOpenDate() < $this->getCloseDate()) {
            $return = range($this->getOpenDate(), $this->getCloseDate());
        } else {
            $return = array_merge(range($this->getOpenDate(), 31), range(1, $this->getCloseDate()));
        }

        return $return;
    }

    /**
     * @Assert\True(
     *     message="admin.error.debit_payment_processor",
     *     groups={
     *         "debit_fee"
     *     }
     * )
     */
    public function isValidPaymentProcessorPerAllowedDebitFee()
    {
        if ($this->getPaymentProcessor() !== PaymentProcessor::ACI && $this->isAllowedDebitFee()) {
            return false;
        }

        return true;
    }

    /**
     * @Assert\True(
     *     message="admin.error.debit_fee_should_be_filled",
     *     groups={
     *         "debit_fee"
     *     }
     * )
     */
    public function isFilledDebitFeePerAllowedDebitFee()
    {
        if (!$this->isAllowedDebitFee()) {
            return true;
        }

        if (!empty($this->debitFee)) {
            return true;
        }

        return false;
    }

    /**
     * @Assert\True(
     *     message="admin.error.should_be_allowed_payment_source_type",
     *     groups={
     *         "allowed_payments_source"
     *     }
     * )
     */
    public function isAllowedAtLeastOnePaymentSourceType()
    {
        return $this->isAllowedCreditCard() || $this->isAllowedACH();
    }

    /**
     * @Assert\True(
     *     message="admin.error.should_be_allowed_credit_card_for_debit",
     *     groups={
     *         "allowed_payments_source"
     *     }
     * )
     */
    public function isAllowedDebitFeeThenShouldBeAllowedCreditCard()
    {
        if ($this->isAllowedDebitFee()) {
            return $this->isAllowedCreditCard();
        }

        return true;
    }
}
