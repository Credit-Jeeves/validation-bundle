<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\CoreBundle\Arf\ArfParser;

class CreditCardUtilizationController extends Controller
{
    public function indexAction()
    {
        $ArfReport = $this->get('core.session.applicant')->getUser()->getReportsPrequal()->last()->getArfReport();
        $nRevolvingDept = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_TOTAL_REVOLVING
        );
        if ($nRevolvingDept == 0) {
            $nAvailableDebt = 0;
        } else {
            $nAvailableDebt = 100 - $ArfReport->getValue(
                ArfParser::SEGMENT_PROFILE_SUMMARY,
                ArfParser::REPORT_TOTAL_REVOLVING_AVAILABLE_PERCENT
            );
        }

        return $this->render(
            'ComponentBundle:CreditCardUtilization:index.html.twig',
            array(
                'nAvailableDebt' => $nAvailableDebt
            )
        );
    }
}
