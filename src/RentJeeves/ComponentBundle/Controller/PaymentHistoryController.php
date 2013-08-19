<?php
namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\DataBundle\Enum\ContractStatus;

class PaymentHistoryController extends Controller
{
    /**
     * @Template("RjComponentBundle:PaymentHistory:index.html.twig")
     * @return multitype:
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\User $user)
    {
        $active = array();
        $finished = array();
        $aMonthes = array();
        for ($i = 1; $i < 13; $i++) {
            $aMonthes[] = date('M', mktime(0, 0, 0, $i));
        }
        $em = $this->get('doctrine.orm.default_entity_manager');//->getRepository('DataBundle:Order');
        $contracts = $user->getContracts();
        foreach ($contracts as $contract) {
            if (ContractStatus::PENDING == $contract->getStatus()) {
                continue;
            }
            $currentDate = new \DateTime('now');
            $startDate = $contract->getStartAt();
            $interval = $startDate->diff($currentDate)->format('%r%a');
            if ($interval < 0) {
                continue;
            }
            $item = array();
            $item['address'] = $contract->getRentAddress($contract->getProperty(), $contract->getUnit());
            $item['rent'] = $contract->getRent();
            $item['start'] = $contract->getStartAt()->format('m/d/Y');
            $item['finish'] = $contract->getFinishAt();
            $item['updated'] = $contract->getUpdatedAt()->format('F d, Y');
            $item['balance_year'] = $contract->getFinishAt()->format('Y');
            $item['balance_month'] = $contract->getFinishAt()->format('m');
            switch ($status = $contract->getStatus()) {
                case ContractStatus::CURRENT:
                    $history = $contract->getFinishedPaymentHistory($em);
                    $item['history'] = $history['history'];
                    $item['last_date'] = $history['last_date'];
                    $item['last_amount'] = $history['last_amount'];
                    $item['status'] = 'ACTIVE';
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
        );
    }
}
