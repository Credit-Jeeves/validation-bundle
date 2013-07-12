<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PersonalInfoController extends Controller
{
    /**
     * @Template()
     * @param \Report $Report
     */
    public function indexAction(Report $Report)
    {
        $dateFullFormat = $this->container->getParameter('date_full');
        
        $sSSN       = $this->get('core.session.applicant')->getUser()->displaySsn();
        $sDOB       = $this->get('core.session.applicant')->getUser()->getDateOfBirth()->format($dateFullFormat);
        $sName      = $Report->getApplicantName();
        $aAddresses = array();
        $aAddresses = $Report->getApplicantAddress();
        if (isset($aAddresses['address_text'])) {
            $aAddress = $this->aAddresses;
            $aAddresses = array();
        }
        $aAddress     = empty($aAddresses) ? array() : array_shift($aAddresses);
        $aEmployments = $Report->getApplicantEmployments();
//         echo '<pre>';
//         print_r($aEmployments);
//         exit;
        return array(
                'sName' => $sName,
                'aAddresses' => $aAddresses,
                'aAddress' => $aAddress,
                'aEmployments' => $aEmployments,
                'sSSN' => $sSSN,
                'sDOB' => $sDOB,
           );
    }
}
