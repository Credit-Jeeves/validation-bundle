<?php
namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $contracts = $user->getContracts();
        foreach ($contracts as $contract) {
            $item = array();
            $item['address'] = $contract->getRentAddress($contract->getProperty(), $contract->getUnit());
            $item['rent'] = $contract->getRent();
            $item['start'] = $contract->getStartAt();
            $item['finish'] = $contract->getFinishAt();
            switch ($status = $contract->getStatus()) {
                case 'approved':
                    $item['history'] = $contract->getFinishedPaymentHistory();
                    $item['status'] = 'ACTIVE';
                    $active[] = $item;
                    break;
                case 'finished':
                    $item['history'] = $contract->getActivePaymentHistory();
                    $item['status'] = 'FINISHED';
                    $finished[] = $item;
                    break;
            }
        }
        // For finished contracts it's a good idea to create history table
//         echo '<pre>';
//         print_r($finished);
//         echo '</pre>';
        
        return array(
            'aActiveContracts' => $active,
            'aFinishedContracts' => $finished,
        );
    }
}
