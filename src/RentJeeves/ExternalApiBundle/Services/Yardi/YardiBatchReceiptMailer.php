<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Mailer\Mailer;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("yardi.receipt_mailer")
 */
class YardiBatchReceiptMailer
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @InjectParams({
     *     "em"      = @Inject("doctrine.orm.default_entity_manager"),
     *     "mailer"  = @Inject("project.mailer")
     * })
     */
    public function __construct(EntityManager $em, Mailer $mailer)
    {
        $this->mailer = $mailer;
        $this->em = $em;
    }

    /**
     * @TODO It's not very nice code, need to find the better solution
     * 1. it's too big method
     * 2. Large investments, pyramid
     *
     * @param array $request
     */
    public function send(array $request, $depositDate)
    {
        foreach ($request as $holdingId => $groups) {
            $globalByHolding = array();
            foreach ($groups as $groupId => $batchIds) {
                $globalByGroup = array();
                foreach ($batchIds as $batchId => $typePayments) {
                    foreach ($typePayments as $typePayment => $status) {
                        if ($typePayment == 'payment_batch_id') {
                            continue;
                        }
                        $data = array();
                        $data['batchId'] = $batchId;
                        $data['payment_batch_id'] = $typePayments['payment_batch_id'];
                        $data['type'] = $typePayment;
                        if ($status[ReceiptBatchSender::REQUEST_SUCCESSFUL] > 0) {
                            $dataSuccessfully = array();
                            $dataSuccessfully['status'] = ReceiptBatchSender::REQUEST_SUCCESSFUL;
                            $dataSuccessfully['total'] = $status[ReceiptBatchSender::REQUEST_SUCCESSFUL];
                            $dataSuccessfully = array_merge($dataSuccessfully, $data);
                            $globalByGroup[] = $dataSuccessfully;
                            $globalByHolding[] = $dataSuccessfully;
                        }
                        if ($status[ReceiptBatchSender::REQUEST_FAILED] > 0) {
                            $dataFailed = array();
                            $dataFailed['status'] = ReceiptBatchSender::REQUEST_FAILED;
                            $dataFailed['total'] = $status[ReceiptBatchSender::REQUEST_FAILED];
                            $dataFailed = array_merge($dataFailed, $data);
                            $globalByGroup[] = $dataFailed;
                            $globalByHolding[] = $dataFailed;
                        }
                    }
                }
                if (empty($globalByGroup)) {
                    continue;
                }
                $emailData = [
                    'deposit_date' => $depositDate,
                    'data' => $globalByGroup,
                ];
                $this->sendEmailToLandlordByGroup($groupId, $emailData);
            }

            if (empty($globalByHolding)) {
                continue;
            }
            $emailData = [
                'deposit_date' => $depositDate,
                'data' => $globalByHolding,
            ];
            $this->sendEmailToLandlordByHolding($holdingId, $emailData);
        }
    }

    /**
     * @param $holdingId
     * @param $data
     */
    protected function sendEmailToLandlordByHolding($holdingId, $data)
    {
        $landlords = $this->em
            ->getRepository('RjDataBundle:Landlord')
            ->getHoldingAdmins($holdingId);
        $this->sendEmailToLandlords($landlords, $data);
    }

    /**
     * @param $groupId
     * @param $data
     */
    protected function sendEmailToLandlordByGroup($groupId, $data)
    {
        $landlords = $this->em
            ->getRepository('RjDataBundle:Landlord')
            ->getLandlordsByGroupNoAdmin($groupId);
        $this->sendEmailToLandlords($landlords, $data);
    }

    /**
     * @param $landlords
     * @param $data
     */
    protected function sendEmailToLandlords($landlords, $data)
    {
        /**
         * @var $landlord Landlord
         */
        foreach ($landlords as $landlord) {
            $this->mailer->sendPushBatchReceiptsReport(
                $landlord,
                $data
            );
        }
    }
}
