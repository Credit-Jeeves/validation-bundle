<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class CreditSummaryController extends Controller
{
    public function indexAction(Report $Report)
    {
        $dateShortFormat = $this->container->getParameter('date_short');
        $sDate = $Report->getCreatedAt()->format($dateShortFormat);

        return $this->render(
            'ComponentBundle:CreditSummary:index.html.twig',
            array(
                'sDate' => $sDate,
                'total_trade_items_counter' => $Report->getTotalAccounts(),
                'satisfactory_accounts' => $Report->getTotalAccounts() - $Report->getTotalDerogatoryAccounts(),
                'now_delinquentderog_counter' => $Report->getTotalDerogatoryAccounts(),
                'oldest_trade_date' => $Report->getOldestTradelineInYears(),
                'monthly_payment' => $Report->getTotalMonthlyPayments(),
                'total_inquiries_counter' => $Report->getNumberOfInquieres(),
                'total_past_due' => $Report->getBalanceOpenCollectionAccounts(),
                'collections' => $Report->getTotalOpenCollectionAccounts(),
                'public_records_count' => $Report->getTotalPublicRecords(),
            )
        );
    }
}
