<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

class PersonalInfoController extends Controller
{
    public function indexAction()
    {
        $Report     = $this->getUser()->getReportsD2c()->last();
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
        
        return $this->render(
            'ComponentBundle:PersonalInfo:index.html.twig',
            array(
                'sName' => $sName,
                'aAddresses' => $aAddresses,
                'aAddress' => $aAddress,
                'aEmployments' => $aEmployments,
                'sSSN' => $sSSN,
                'sDOB' => $sDOB,
               )
           );
    }
}
