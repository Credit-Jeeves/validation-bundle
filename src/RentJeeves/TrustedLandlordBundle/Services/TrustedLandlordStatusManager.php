<?php

namespace RentJeeves\TrustedLandlordBundle\Services;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;
use RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordStatusException;

/**
 * trusted_landlord.status_manager
 */
class TrustedLandlordStatusManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $paymentUntilTime;

    /**
     * @var string
     */
    protected $debitPaymentUntilTime;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param Mailer $mailer
     * @param string $paymentUntilTime
     * @param string $debitPaymentUntilTime
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        Mailer $mailer,
        $paymentUntilTime,
        $debitPaymentUntilTime
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->paymentUntilTime = $paymentUntilTime;
        $this->debitPaymentUntilTime = $debitPaymentUntilTime;
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @param string $newStatus
     * @throws TrustedLandlordStatusException
     *
     * @return bool
     */
    public function updateStatus(TrustedLandlord $trustedLandlord, $newStatus)
    {
        if (!TrustedLandlordStatus::isValid($newStatus)) {
            $this->logger->debug(
                sprintf(
                    'TrustedLandlord#%s not process such status %s',
                    $trustedLandlord->getId(),
                    $newStatus
                )
            );

            return false;
        }

        if ($trustedLandlord->getStatus() === $newStatus) {
            $this->logger->debug(
                sprintf(
                    'TrustedLandlord#%s already in this status %s',
                    $trustedLandlord->getId(),
                    $trustedLandlord->getStatus()
                )
            );

            return false;
        }

        if (in_array($trustedLandlord->getStatus(), [TrustedLandlordStatus::TRUSTED, TrustedLandlordStatus::DENIED])) {
            $message = sprintf(
                'We couldn\'t change trusted/denied status to %s for TrustedLandlord#%s',
                $newStatus,
                $trustedLandlord->getId()
            );
            $this->logger->alert($message);

            throw new TrustedLandlordStatusException($message);
        }

        $trustedLandlord->setStatus($newStatus);
        $method = sprintf('handle%sStatus', str_replace(' ', '', ucwords($newStatus)));
        $result = $this->$method($trustedLandlord);
        $this->em->flush();

        return $result;
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @return bool
     */
    protected function handleInProgressStatus(TrustedLandlord $trustedLandlord)
    {
        $this->logger->debug(
            sprintf('TrustedLandlord#%s got new status %s', $trustedLandlord->getId(), $trustedLandlord->getStatus())
        );

        return true;
    }


    /**
     * @param TrustedLandlord $trustedLandlord
     * @return bool
     */
    protected function handleFailedStatus(TrustedLandlord $trustedLandlord)
    {
        $this->logger->alert(
            sprintf('TrustedLandlord#%s got new status %s.', $trustedLandlord->getId(), $trustedLandlord->getStatus())
        );

        return true;
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @return bool
     */
    protected function handleWaitingForInfoStatus(TrustedLandlord $trustedLandlord)
    {
        $this->logger->debug(
            sprintf('TrustedLandlord#%s got new status %s', $trustedLandlord->getId(), $trustedLandlord->getStatus())
        );

        return true;
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @return bool
     */
    protected function handleTrustedStatus(TrustedLandlord $trustedLandlord)
    {
        $payments = $this->em->getRepository('RjDataBundle:Payment')->findAllFlaggedPaymentToUntrustedLandlord(
            $trustedLandlord->getGroup()
        );
        $today = new \DateTime();
        foreach ($payments as $payment) {
            $currentStartDate = $payment->getStartDate();
            $paymentType = $payment->getPaymentAccount()->getType();
            if ($paymentType === PaymentAccountType::DEBIT_CARD) {
                list($hours, $minutes) = explode(':', $this->debitPaymentUntilTime);
            } else {
                list($hours, $minutes) = explode(':', $this->paymentUntilTime);
            }

            $today->setTime($hours, $minutes, 0);

            /**
             * Update payment start_date here if payment_date is passed while this landlord was being checked
             * (note that you need to update date to tomorrow if after cutoff time)
             */
            if ($today > $currentStartDate) {
                $tomorrow = new \DateTime('+1 day');
                $tomorrow->setTime('06', '00', '00');
                $payment->setStartDate($tomorrow);
            }

            $this->mailer->sendTrustedLandlordApproved($payment);
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @return bool
     */
    protected function handleDeniedStatus(TrustedLandlord $trustedLandlord)
    {
        $activePayments = $this->em->getRepository('RjDataBundle:Payment')->findAllActiveAndFlaggedPaymentsForGroup(
            $trustedLandlord->getGroup()
        );
        foreach ($activePayments as $activePayment) {
            $activePayment->setClosed($this, 'We were unable to verify your Property Manager');
            $this->mailer->sendTrustedLandlordDenied($activePayment);
        }
        $this->em->flush();

        return true;
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @return bool
     */
    protected function handleNewStatus(TrustedLandlord $trustedLandlord)
    {
        if ($trustedLandlord->getJiraMapping()) {
            $this->logger->debug(
                sprintf('TrustedLandlord#%s already has mapping.', $trustedLandlord->getId())
            );

            return false;
        }

        $job = new Job('renttrack:jira-api:create-issue', ['--trusted-landlord-id='.$trustedLandlord->getId()]);
        $this->em->persist($job);
        $this->em->flush();

        return true;
    }
}
