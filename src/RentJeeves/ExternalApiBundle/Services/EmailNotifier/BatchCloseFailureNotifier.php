<?php

namespace RentJeeves\ExternalApiBundle\Services\EmailNotifier;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\ExternalApiBundle\Model\EmailNotifier\BatchCloseFailureDetail;
use RentJeeves\ExternalApiBundle\Services\EmailNotifier\Exception\NotifierException;

class BatchCloseFailureNotifier
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
     * @param string $accountingSystemBatchNumber = null
     */
    public function notify(Holding $holding, $accountingSystemBatchNumber =  null)
    {
        $this->logger->debug(
            sprintf(
                'Start notify about batch close failed per holding#%s',
                $holding->getId()
            )
        );

        $failureJobs = $this->getFailedPushJobsToExternalApi($holding);
        if (empty($failureJobs)) {
            $this->logger->debug(
                sprintf('We don\'t have failure jobs per holding#%s, so nothing to send', $holding->getId())
            );

            return;
        }

        $batchCloseFailureModels = $this->mapJobsToBatchCloseFailureDetail(
            $holding,
            $failureJobs,
            $accountingSystemBatchNumber
        );

        $pathToCsvFileReport = $this->getPathToCsvFileReport($holding);

        $this->sendEmail(
            $holding,
            $batchCloseFailureModels,
            $pathToCsvFileReport
        );

        unlink($pathToCsvFileReport);

        $this->logger->debug(
            sprintf(
                'Finish notify about batch close failed per holding#%s',
                $holding->getId()
            )
        );
    }

    /**
     * @param Holding $holding
     * @return \RentJeeves\DataBundle\Entity\JobRelatedOrder[]
     */
    protected function getFailedPushJobsToExternalApi(Holding $holding)
    {
        return $this->em->getRepository('RjDataBundle:JobRelatedOrder')->getFailedPushJobsToExternalApi(
            $holding,
            new \DateTime()
        );
    }

    /**
     * @param Holding $holding
     * @return string
     * @throws \RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException
     */
    protected function getPathToCsvFileReport(Holding $holding)
    {
        $content = $this->getRentTrackExportContent($holding);
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
     * @param Holding $holding
     * @return \Doctrine\Common\Collections\ArrayCollection|mixed
     */
    protected function getRentTrackExportContent(Holding $holding)
    {
        $today = new \DateTime();

        return $this->exporter->getContent([
            'holding' => $holding,
            'begin' => $today->format('Y-m-d'),
            'end' => $today->format('Y-m-d')
        ]);
    }

    /**
     * @param Holding $holding
     * @param array $failureJobs
     * @param string $accountingSystemBatchNumber
     * @return BatchCloseFailureDetail[]
     */
    protected function mapJobsToBatchCloseFailureDetail(Holding $holding, $failureJobs, $accountingSystemBatchNumber = null)
    {
        $result = [];
        /** @var JobRelatedOrder $job */
        foreach ($failureJobs as $job) {
            $batchCloseFailure = new BatchCloseFailureDetail();
            $batchCloseFailure->setPaymentDate($job->getOrder()->getCreatedAt());
            $batchCloseFailure->setRentTrackBatchNumber($job->getOrder()->getTransactionBatchId());
            $batchCloseFailure->setResidentId(
                $job->getOrder()->getContract()->getTenant()->getResidentForHolding($holding)
            );
            $batchCloseFailure->setTransactionId($job->getOrder()->getTransactionId());
            $batchCloseFailure->setResidentName($job->getOrder()->getContract()->getTenant()->getFullName());
            $batchCloseFailure->setAccountingSystemBatchNumber($accountingSystemBatchNumber);

            $result[] = $batchCloseFailure;
        }

        return $result;
    }

    /**
     * @param Holding $holding
     * @param BatchCloseFailureDetail[] $batchCloseFailureDetail
     * @param string $filePath
     */
    protected function sendEmail(Holding $holding, $batchCloseFailureDetail, $filePath)
    {
        $this->logger->debug('Send email about failed push per holding#%s');

        /** @var Landlord $landlord */
        foreach ($holding->getLandlords() as $landlord) {
            $result = $this->mailer->sendPostPaymentError($landlord, $batchCloseFailureDetail, $filePath);

            if ($result === false) {
                $this->logger->debug(
                    sprintf('Can not send  send email to %s about failure batch close', $landlord->getEmail())
                );
            } else {
                $this->logger->debug(
                    sprintf('Email to %s about failure batch close was successfully sent', $landlord->getEmail())
                );
            }
        }

        $this->logger->debug('Finish send email about failed push per holding#%s');
    }
}

