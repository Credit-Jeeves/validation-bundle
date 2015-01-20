<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Enum\HardInquiriesPeriod;

class HardInquiriesController extends Controller
{
    /**
     * @var integer
     */
    const MAX_DIAL = 12;

    public function indexAction(Report $Report)
    {
        $inquiries = $Report->getNumberOfInquieres();
        $maxDial = self::MAX_DIAL;
        if ($inquiries > $maxDial) {
            $maxDial = $inquiries;
        }

        /** @var HardInquiriesPeriod timePeriod
         *
         *  Make sure that there is a translation in messages bundle for all possible values for timePeriod
         */
        $timePeriod = $Report->getInquiriesPeriod();
        $translator = $this->get('translator.default');
        $headerString = $translator->trans("inquiries.hard.header." . $timePeriod);
        $commentString = $translator->trans("inquiries.hard.comment." . $timePeriod);

        return $this->render(
            'ComponentBundle:HardInquiries:index.html.twig',
            array(
                'inquiries' => $inquiries,
                'headerString' => $headerString,
                'commentString' => $commentString,
                'maxDial' => $maxDial,
            )
        );
    }
}
