<?php

namespace RentJeeves\ExternalApiBundle\Services\EmailNotifier;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
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
            $parameters['accounting-batch-id'] = $accountingBatchId;
        }

        /** @var Group $group */
        foreach ($holding->getGroups() as $group) {
            if (!$this->isExistFailedPushPaymentJobsToExternalApi($group)) {
                $this->logger->debug(sprintf('Don\'t have failed jobs about payment push group#%s', $group->getId()));
                continue;
            }

            $parameters['--group-id'] = $group->getId();
            $job = new Job('renttrack:notify:batch-close-failure', $parameters);
            $this->em->persist($job);

            $this->logger->debug(sprintf('We have failure jobs about payment push group#%s', $group->getId()));
        }

        $this->em->flush();
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function isExistFailedPushPaymentJobsToExternalApi(Group $group)
    {
        return count($this->getFailedPushPaymentJobsToExternalApi($group)) > 0;
    }

    /**
     * @param Group $group
     * @param string $accountingSystemBatchNumber
     */
    public function notify(Group $group, $accountingSystemBatchNumber = null)
    {
        $this->logger->debug(
            sprintf(
                'Start notify about batch close failed per Group#%s',
                $group->getId()
            )
        );

        $failureJobs = $this->getFailedPushPaymentJobsToExternalApi($group);
        if (empty($failureJobs)) {
            $this->logger->debug(
                sprintf('We don\'t have failure jobs per Group#%s, so nothing to send', $group->getId())
            );

            return;
        }

        $batchCloseFailureModels = $this->mapJobsToFailedPostPaymentDetail(
            $group,
            $failureJobs,
            $accountingSystemBatchNumber
        );

        $pathToCsvFileReport = $this->getPathToCsvFileReport($group);

        $this->sendEmails(
            $group,
            $batchCloseFailureModels,
            $pathToCsvFileReport
        );

        unlink($pathToCsvFileReport);

        $this->logger->debug(
            sprintf(
                'Finish notify about batch close failed per Group#%s',
                $group->getId()
            )
        );
    }

    /**
     * @param Group $group
     * @return \RentJeeves\DataBundle\Entity\JobRelatedOrder[]
     */
    protected function getFailedPushPaymentJobsToExternalApi(Group $group)
    {
        return $this->em->getRepository('RjDataBundle:JobRelatedOrder')->getFailedPushJobsToExternalApi(
            $group,
            new \DateTime()
        );
    }

    /**
     * @param Group $group
     * @return string
     * @throws \RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException
     */
    protected function getPathToCsvFileReport(Group $group)
    {
        $content = $this->getRentTrackExportContent($group);
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
     * @param Group $group
     * @return \Doctrine\Common\Collections\ArrayCollection|mixed
     */
    protected function getRentTrackExportContent(Group $group)
    {
        $today = new \DateTime();

        return $this->exporter->getContent([
            'group' => $group,
            'begin' => $today->format('Y-m-d'),
            'end' => $today->format('Y-m-d')
        ]);
    }

    /**
     * @param Group $group
     * @param array $failureJobs
     * @param string $accountingSystemBatchNumber
     * @return FailedPostPaymentDetail[]
     */
    protected function mapJobsToFailedPostPaymentDetail(Group $group, $failureJobs, $accountingSystemBatchNumber = null)
    {
        $result = [];
        /** @var JobRelatedOrder $job */
        foreach ($failureJobs as $job) {
            $batchCloseFailure = new FailedPostPaymentDetail();
            $batchCloseFailure->setPaymentDate($job->getOrder()->getCreatedAt());
            $batchCloseFailure->setRentTrackBatchNumber($job->getOrder()->getTransactionBatchId());
            $batchCloseFailure->setResidentId(
                $job->getOrder()->getContract()->getTenant()->getResidentForHolding($group->getHolding())
            );
            $batchCloseFailure->setTransactionId($job->getOrder()->getTransactionId());
            $batchCloseFailure->setResidentName($job->getOrder()->getContract()->getTenant()->getFullName());
            $batchCloseFailure->setAccountingSystemBatchNumber($accountingSystemBatchNumber);

            $result[] = $batchCloseFailure;
        }

        return $result;
    }

    /**
     * @param Group $group
     * @param FailedPostPaymentDetail[] $batchCloseFailureDetail
     * @param string $filePath
     */
    protected function sendEmails(Group $group, $batchCloseFailureDetail, $filePath)
    {
        $this->logger->debug('Send email about failed push per Group#%s');
        $landlords = $this->em->getRepository('RjDataBundle:Landlord')->getLandlordsByGroup($group);
        /** @var Landlord $landlord */
        foreach ($landlords as $landlord) {
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

        $this->logger->debug('Finish send email about failed push per Group#%s');
    }
}
