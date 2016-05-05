<?php

namespace RentJeeves\ExternalApiBundle\Services\EmailNotifier;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\ExternalApiBundle\Model\EmailNotifier\FailedPostPaymentDetail;

class FailedPostPaymentNotifier
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
     * @var RentTrackExportReport
     */
    protected $exporter;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param RentTrackExportReport $rentTrackExportReport
     * @param Mailer $mailer
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        RentTrackExportReport $rentTrackExportReport,
        Mailer $mailer
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->exporter = $rentTrackExportReport;
        $this->mailer = $mailer;
    }

    /**
     * @param Holding $holding
     * @param string $accountingBatchId
     */
    public function createNotifierAboutFailedPostPaymentJob(Holding $holding, $accountingBatchId = null)
    {
        $parameters = [];

        if ($accountingBatchId) {
            $parameters[] = '--accounting-batch-id=' . $accountingBatchId;
        }

        if (!$this->isExistFailedPushPaymentJobsToExternalApi($holding)) {
            $this->logger->debug(sprintf('Don\'t have failed jobs about payment push holding#%s', $holding->getId()));

            return;
        }

        $parameters[] = '--holding-id=' . $holding->getId();
        $job = new Job('renttrack:notify:batch-close-failure', $parameters);
        $this->em->persist($job);

        $this->logger->debug(sprintf('We have failure jobs about payment push holding#%s', $holding->getId()));

        $this->em->flush();
    }

    /**
     * @param Holding $holding
     * @return bool
     */
    public function isExistFailedPushPaymentJobsToExternalApi(Holding $holding)
    {
        $groupsId = $this->convertGroupsToArrayIds($holding->getGroups());

        return count($this->getFailedPushPaymentJobsToExternalApi($groupsId)) > 0;
    }

    /**
     * @param Holding $holding
     * @param string $accountingSystemBatchNumber
     */
    public function notify(Holding $holding, $accountingSystemBatchNumber = null)
    {
        $this->logger->debug(
            sprintf(
                'Start notify about batch close failed per Holding#%s',
                $holding->getId()
            )
        );

        $this->notifyHoldingAdmins($holding, $accountingSystemBatchNumber);
        $this->notifyHoldingNoneAdmins($holding, $accountingSystemBatchNumber);

        $this->logger->debug(
            sprintf(
                'Finish notify about batch close failed per Holding#%s',
                $holding->getId()
            )
        );
    }

    /**
     * @param Holding $holding
     * @param string|null $accountingSystemBatchNumber
     */
    protected function notifyHoldingNoneAdmins(Holding $holding, $accountingSystemBatchNumber = null)
    {
        $landlords = $this->em->getRepository("RjDataBundle:Landlord")->getHoldingNoneAdmins($holding->getId());
        /** @var Landlord $landlord */
        foreach ($landlords as $landlord) {
            $groups = $landlord->getGroups();
            $failureJobs = $this->getFailedPushPaymentJobsToExternalApi(
                $groupsId = $this->convertGroupsToArrayIds($groups)
            );

            if (empty($failureJobs)) {
                $this->logger->debug(
                    sprintf(
                        'We don\'t have failure jobs per Groups#%s, so nothing to send',
                        implode(',', $groupsId)
                    )
                );

                return;
            }

            $batchCloseFailureModels = $this->mapJobsToFailedPostPaymentDetail(
                $landlord->getHolding(),
                $failureJobs,
                $accountingSystemBatchNumber
            );

            $this->doNotify($landlord, $groups, $batchCloseFailureModels);
        }
    }

    /**
     * @param Holding $holding
     * @param string|null $accountingSystemBatchNumber
     */
    protected function notifyHoldingAdmins(Holding $holding, $accountingSystemBatchNumber = null)
    {
        $failureJobs = $this->getFailedPushPaymentJobsToExternalApi(
            $this->convertGroupsToArrayIds($holding->getGroups())
        );
        if (empty($failureJobs)) {
            $this->logger->debug(
                sprintf('We don\'t have failure jobs per Holding#%s, so nothing to send', $holding->getId())
            );

            return;
        }

        $batchCloseFailureModels = $this->mapJobsToFailedPostPaymentDetail(
            $holding,
            $failureJobs,
            $accountingSystemBatchNumber
        );

        $landlords = $this->em->getRepository('RjDataBundle:Landlord')->getHoldingAdmins($holding->getId());

        foreach ($landlords as $landlord) {
            $this->doNotify($landlord, $holding->getGroups(), $batchCloseFailureModels);
        }
    }

    /**
     * @param Landlord $landlord
     * @param Collection $groups
     * @param array $batchCloseFailureModels
     */
    protected function doNotify(Landlord $landlord, Collection $groups, array $batchCloseFailureModels)
    {
        $pathToCsvFileReport = $this->getPathToCsvFileReport($groups);

        $this->sendEmails(
            $landlord,
            $batchCloseFailureModels,
            $pathToCsvFileReport
        );

        unlink($pathToCsvFileReport);

        $this->logger->debug(
            sprintf(
                'Sent email to %s %s about failure batch close',
                $landlord->isSuperAdmin() ? 'holdingAdmin' : 'noneAdmin',
                $landlord->getEmail()
            )
        );
    }

    /**
     * @param array $groups
     * @return \RentJeeves\DataBundle\Entity\JobRelatedOrder[]
     */
    protected function getFailedPushPaymentJobsToExternalApi(array $groups)
    {
        return $this->em->getRepository('RjDataBundle:JobRelatedOrder')->getFailedPushJobsToExternalApi(
            $groups,
            new \DateTime()
        );
    }

    /**
     * @param Collection $groups
     * @return string
     * @throws \RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException
     */
    protected function getPathToCsvFileReport(Collection $groups)
    {
        $content = $this->getRentTrackExportContent($groups);
        $tmpFilePath = sprintf(
            '%s%s%s_%s',
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            uniqid(),
            $this->exporter->getFilename()
        );

        $handle = fopen($tmpFilePath, "w");
        fwrite($handle, $content);
        fclose($handle);

        return $tmpFilePath;
    }


    /**
     * @param Collection $groups
     * @return Collection|mixed
     */
    protected function getRentTrackExportContent(Collection $groups)
    {
        $today = new \DateTime();

        return $this->exporter->getContent([
            'groups' => $groups,
            'begin' => $today->format('Y-m-d'),
            'end' => $today->format('Y-m-d')
        ]);
    }

    /**
     * @param Holding $holding
     * @param array $failureJobs
     * @param string $accountingSystemBatchNumber
     * @return FailedPostPaymentDetail[]
     */
    protected function mapJobsToFailedPostPaymentDetail(
        Holding $holding,
        array $failureJobs,
        $accountingSystemBatchNumber = null
    ) {
        $result = [];
        /** @var JobRelatedOrder $job */
        foreach ($failureJobs as $job) {
            $batchCloseFailure = new FailedPostPaymentDetail();
            $batchCloseFailure->setPaymentDate($job->getOrder()->getCreatedAt());
            $batchCloseFailure->setRentTrackBatchNumber($job->getOrder()->getTransactionBatchId());
            $residentMapping = $job->getOrder()->getContract()->getTenant()->getResidentForHolding($holding);
            if ($residentMapping) {
                $batchCloseFailure->setResidentId($residentMapping->getResidentId());
            }
            $batchCloseFailure->setTransactionId($job->getOrder()->getTransactionId());
            $batchCloseFailure->setResidentName($job->getOrder()->getContract()->getTenant()->getFullName());
            $batchCloseFailure->setAccountingSystemBatchNumber($accountingSystemBatchNumber);

            $result[] = $batchCloseFailure;
        }

        return $result;
    }

    /**
     * @param Landlord $landlord
     * @param FailedPostPaymentDetail[] $batchCloseFailureDetail
     * @param string $filePath
     */
    protected function sendEmails(Landlord $landlord, array $batchCloseFailureDetail, $filePath)
    {
        $this->logger->debug('Send email about failed push for landlord#' . $landlord->getEmail());
        $result = $this->mailer->sendPostPaymentError($landlord, $batchCloseFailureDetail, $filePath);

        if ($result === false) {
            $this->logger->debug(
                sprintf('Can\'nt send  send email to %s about failure batch close', $landlord->getEmail())
            );
        } else {
            $this->logger->debug(
                sprintf('Email to %s about failure batch close was successfully sent', $landlord->getEmail())
            );
        }
    }

    /**
     * @param array $groups
     * @return array
     */
    protected function convertGroupsToArrayIds(Collection $groups)
    {
        $ids = [];

        foreach ($groups as $group) {
            $ids[] = $group->getId();
        }

        return $ids;
    }
}
