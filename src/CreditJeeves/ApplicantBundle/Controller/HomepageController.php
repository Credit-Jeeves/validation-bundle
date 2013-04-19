<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\DataBundle\Entity\Lead;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
        $nLeads = $User->getUserLeads()->count();
        $Lead    = $this->get('core.session.applicant')->getLead();
        $nTarget = $Lead->getTargetScore();
        $nScore  = $User->getScores()->last()->getScore();
        $isSuccess = false;
        if ($nScore >= $nTarget) {
            $isSuccess = true;
        }
        $Report  = $User->getReportsPrequal()->last();
        $sEmail  = $User->getEmail();
        return array(
            'Report' => $Report,
            'Lead' => $Lead,
            'sEmail' => $sEmail,
            'isSuccess' => $isSuccess,
            'nLeads' => $nLeads,
            );
    }

    /**
     * @Route(
     *  "/lead",
     *  name="lead_change",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"}
     * )
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function changeAction()
    {
        $nLeadId = $this->getRequest()->get('lead_id');
        $this->get('core.session.applicant')->setLeadId($nLeadId);
        return new JsonResponse('');
    }
    
    
    /**
     * @Route(
     *     "/incentives/ajax",
     *      name="insentives_ajax",
     *      defaults={"_format"="json"},
     *      requirements={"_format"="html|json"}
     * )
     * @Method(
     *     {"GET", "POST"}
     * )
     *
     * @return array
     */
    public function someAction()
    {
    
    }
        
}
