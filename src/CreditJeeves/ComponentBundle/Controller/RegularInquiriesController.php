<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class RegularInquiriesController extends Controller
{
    /**
     *
     * This component currently only supports Experian prequal reports
     *
     * @Template()
     * @param \Report $Report
     */
    public function indexAction(Report $Report)
    {
        $aInquiries = $Report->getApplicantInquiries();
        return array(
                'aInquiries' => $aInquiries,
            );
    }
}
