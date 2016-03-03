<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\ExternalApiBundle\Model\EmailNotifier\BatchCloseFailure;

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
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param RentTrackExportReport $rentTrackExportReport
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        RentTrackExportReport $rentTrackExportReport
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->exporter = $rentTrackExportReport;
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

        $failureJobs = $this->getFailureJobs($holding);
        $batchCloseFailureModels = $this->mapJobsToBatchCloseFailure(
            $holding,
            $failureJobs,
            $accountingSystemBatchNumber
        );

        $this->sendEmail(
            $holding,
            $batchCloseFailureModels,
            $this->getRentTrackExportContent($holding)
        );

        $this->logger->debug(
            sprintf(
                'Finish notify about batch close failed per holding#%s for email: %s',
                $holding->getId()
            )
        );
    }

    /**
     * @param Holding $holding
     * @return \RentJeeves\DataBundle\Entity\JobRelatedOrder[]
     */
    protected function getFailureJobs(Holding $holding)
    {
        return $this->em->getRepository('RjDataBundle:JobRelatedOrder')->getFailureOrder(
            $holding,
            new \DateTime()
        );
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
     * @return BatchCloseFailure
     */
    protected function mapJobsToBatchCloseFailure(Holding $holding, $failureJobs, $accountingSystemBatchNumber = null)
    {
        $result = [];
        /** @var JobRelatedOrder $job */
        foreach ($failureJobs as $job) {
            $batchCloseFailure = new BatchCloseFailure();
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

        return $batchCloseFailure;
    }

    /**
     * @param Holding $holding
     * @param BatchCloseFailure[] $batchCloseFailureModels
     * @param string $fileContent
     */
    protected function sendEmail(Holding $holding, $batchCloseFailureModels, $fileContent)
    {
        $this->logger->debug('Send email about failed push per holding#%s to landlord %s');
    }
}

