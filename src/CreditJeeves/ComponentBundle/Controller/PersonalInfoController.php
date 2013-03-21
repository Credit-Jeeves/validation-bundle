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
        $sSSN       = $this->getUser()->displaySsn();
        $sDOB       = $this->getUser()->getDateOfBirth()->format("F j, Y");
        $sName      = $Report->getApplicantName();
        $aAddresses = $Report->getApplicantAddress();
        if (isset($aAddresses['address_text'])) {
            $aAddress = $this->aAddresses;
            $aAddresses = array();
        }
        $aAddress     = empty($aAddresses) ? array() : array_shift($aAddresses);
        $aEmployments = $Report->getApplicantEmployments();
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
