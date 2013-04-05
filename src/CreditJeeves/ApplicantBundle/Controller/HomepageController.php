<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\DataBundle\Entity\Lead;

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
        $User    = $this->get('core.session.applicant')->getUser();
        $Lead    = $this->get('core.session.applicant')->getLead();
        $nTarget = $Lead->getTargetScore();
        $nScore  = $User->getScores()->last()->getScore();
        $isSuccess = false;
        if ($nScore >= $nTarget) {
            $isSuccess = true;
        }
        //echo $nTarget.'-'.$nScore;
        $Report  = $User->getReportsPrequal()->last();
        $sEmail  = $User->getEmail();
        return array(
            'Report' => $Report,
            'Lead' => $Lead,
            'sEmail' => $sEmail,
            'isSuccess' => $isSuccess,
            );
    }
}
