<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\CoreBundle\Arf\ArfParser;

class HardInquiriesController extends Controller
{
    /**
     *
     * @var integer
     */
    const MAX_DIAL = 12;
    
    public function indexAction()
    {
        $ArfReport = $this->get('core.session.applicant')->getUser()->getReportsPrequal()->last()->getArfReport();
        $nInquiries = $ArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_INQUIRIES_DURING_LAST_6_MONTHS_COUNTER
            );
        $nMaxDial = self::MAX_DIAL;
        if ($nInquiries > $nMaxDial) {
            $nMaxDial = $nInquiries;
        }
        return $this->render(
            'ComponentBundle:HardInquiries:index.html.twig',
            array(
                'nInquiries' => $nInquiries,
                'nMaxDial' => $nMaxDial,
                )
            );
    }
}
