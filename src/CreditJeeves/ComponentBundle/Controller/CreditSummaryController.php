<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class CreditSummaryController extends Controller
{
    public function indexAction(Report $Report)
    {
        $dateShortFormat = $this->container->getParameter('date_short');
        $date = $Report->getCreatedAt()->format($dateShortFormat);

        return $this->render(
            'ComponentBundle:CreditSummary:index.html.twig',
            array(
                'date' => $date,
                'totalTradeItemsCounter' => $Report->getTotalAccounts(),
                'satisfactoryAccounts' => $Report->getTotalAccounts() - $Report->getTotalDerogatoryAccounts(),
                'nowDelinquentderogCounter' => $Report->getTotalDerogatoryAccounts(),
                'oldestTradeDate' => $Report->getOldestTradelineInYears(),
                'monthlyPayment' => $Report->getTotalMonthlyPayments(),
                'totalInquiriesCounter' => $Report->getNumberOfInquieres(),
                'totalPastDue' => $Report->getBalanceOpenCollectionAccounts(),
                'collections' => $Report->getTotalOpenCollectionAccounts(),
                'publicRecordsCount' => $Report->getTotalPublicRecords(),
            )
        );
    }
}
