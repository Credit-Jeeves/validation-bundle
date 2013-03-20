<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Alex
 * @Route("/")
 */
class HomepageController extends Controller
{
    /**
     * @Route("/", name="applicant_homepage")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $Lead   = $this->getUser()->getActiveLead();
//        $Dealer = $Lead->getDealer();
//          echo $Dealer->getFirstName().'<br>';
        $Group  = $Lead->getGroup();
//          echo $Group->getName().'<br>';
        $aDealers = $Group->getGroupDealers();
         echo $aDealers->count().'<br>';
        foreach ($aDealers as $aDealer) {
            echo $aDealer->getFirstName().'<br>';
        }
        echo $Lead->getGroup()->getType();
        //$Report = $this->getUser()->getReportsD2c()->last();
        $Report = $this->getUser()->getReportsPrequal()->last();
        $sEmail = $this->getUser()->getEmail();
        return array(
            'Report' => $Report,
            'Lead' => $Lead,
            'sEmail' => $sEmail
            );
    }
}
