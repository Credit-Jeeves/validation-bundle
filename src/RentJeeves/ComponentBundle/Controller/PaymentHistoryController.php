<?php
namespace RentJeeves\ComponentBundle\Controller;

use JMS\Serializer\SerializationContext;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\DataBundle\Enum\ContractStatus;

class PaymentHistoryController extends Controller
{
    /**
     * @Template("RjComponentBundle:PaymentHistory:index.html.twig")
     * @return mixed
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\User $user, $short = false)
    {
        $active = array();
        $finished = array();
        $aMonthes = array();
        for ($i = 1; $i < 13; $i++) {
            $aMonthes[] = date('M', mktime(0, 0, 0, $i, 1));
        }
        $em = $this->get('doctrine.orm.default_entity_manager');
        $translator = $this->get('translator.default');
        $contracts = $user->getContracts();
        /**
         * @var $contract Contract
         */
        foreach ($contracts as $contract) {
            $status = $contract->getStatus();
            if (in_array($status, array(ContractStatus::PENDING, ContractStatus::DELETED, ContractStatus::INVITE))) {
                continue;
            }
            $currentDate = new \DateTime('now');
            $startDate = $contract->getStartAt();
            $finishedDate = $contract->getFinishAt();

            if (!$startDate || !$finishedDate) {
                continue;
            }

            $interval = $startDate->diff($currentDate)->format('%r%a');
            $item = array();
            $item['id'] = $contract->getId();
            $item['address'] = $contract->getRentAddress($contract->getProperty(), $contract->getUnit());
            $item['rent'] = $contract->getRent();
            $item['start'] = $contract->getStartAt()->format('m/d/Y');
            $item['finish'] = $finishedDate;
            $item['updated'] = $contract->getUpdatedAt()->format('F d, Y');
            $item['balance_year'] = '-';
            $item['balance_month'] = '-';

            if ($finishedDate) {
                $item['balance_year'] = $finishedDate->format('Y');
                $item['balance_month'] = $finishedDate->format('m');
            }
            $item['tenant'] = $contract->getTenant()->getFullName();
            switch ($status = $contract->getStatus()) {
                case ContractStatus::APPROVED:
                    $history = $contract->getFuturePaymentHistory($em);
                    $item['history'] = $history['history'];
                    $item['last_date'] = $history['last_date'];
                    $item['last_amount'] = $history['last_amount'];
                    $item['status'] = $translator->trans('contract.status.pay');
                    $item['reporting'] = $contract->getReporting();
                    $active[] = $item;
                    break;
                case ContractStatus::CURRENT:
                    $history = $contract->getFinishedPaymentHistory($em);
                    $item['history'] = $history['history'];
                    $item['last_date'] = $history['last_date'];
                    $item['last_amount'] = $history['last_amount'];
                    $item['status'] = $translator->trans('contract.status.current');
                    $item['reporting'] = $contract->getReporting();
                    $active[] = $item;
                    break;
                case ContractStatus::FINISHED:
                    $history = $contract->getActivePaymentHistory($em);
                    $item['history'] = $history['history'];
                    $item['last_date'] = $history['last_date'];
                    $item['last_amount'] = $history['last_amount'];
                    $item['status'] = 'FINISHED';
                    $finished[] = $item;
                    break;
            }
        }
        return array(
            'aActiveContracts' => $active,
            'aFinishedContracts' => $finished,
            'aMonthes' => $aMonthes,
            'short' => $short,
        );
    }

    /**
     * @Template
     */
    public function paymentsAction(Tenant $user)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('tenantPayment');

        $orders = $this->getDoctrine()->getManager()
            ->getRepository('DataBundle:Order')->getTenantPayments($this->getUser());
        $data = $this->get('jms_serializer')->serialize(array('payments' => $orders), 'json', $context);

        return array('payments' => $data);
    }
}
