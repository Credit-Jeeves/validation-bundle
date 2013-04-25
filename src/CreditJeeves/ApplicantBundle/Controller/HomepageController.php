<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\Tradeline;
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
    public function incentiveAction()
    {
        $aResult = array('id' => 0, 'incentive' => '');
        $request = $this->get('request');
        $this->createNotFoundException();
        $nTradelineId = $request->get('tradeline');
        $sAction      = $request->get('do_action');
        if (empty($nTradelineId) || empty($sAction)) {
            return new JsonResponse('error');
        }
        $oApplicantTradeline = $this->getDoctrine()->getRepository('DataBundle:Tradeline')->find($nTradelineId);
        switch ($sAction) {
            case 'fixed':
                $oApplicantTradeline->setIsFixed(true);
                break;
            case 'disputed':
                $oApplicantTradeline->setIsDisputed(true);
                break;
            case 'completed':
                $oApplicantTradeline->setIsCompleted(true);
                break;
            case 'rollback':
                $oApplicantTradeline->setIsFixed(false);
                $oApplicantTradeline->setIsDisputed(false);
                break;
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($oApplicantTradeline);
        $em->flush();
        $this->changeTradelinesStatus($oApplicantTradeline);
        $aResult['id'] = $oApplicantTradeline->getId();
        $aResult['incentive'] = '';
        return new JsonResponse($aResult);
    }

    private function changeTradelinesStatus($oApplicantTradeline)
    {
        $this->checkLeadsTradeline($oApplicantTradeline);
        $sTradelineHash = $oApplicantTradeline->getTradeline();
        $tradelines = $this->
            getDoctrine()->
            getRepository('DataBundle:Tradeline')->
            findBy(array('tradeline' => $sTradelineHash));
        $em = $this->getDoctrine()->getManager();
        foreach ($tradelines as $tradeline) {
            $tradeline->setIsFixed($oApplicantTradeline->getIsFixed());
            $tradeline->setIsDisputed($oApplicantTradeline->getIsDisputed());
            $tradeline->setIsCompleted($oApplicantTradeline->getIsCompleted());
            
            $em->persist($tradeline);
            $em->flush();
        }
    }

    private function checkLeadsTradeline($oApplicantTradeline)
    {
        $User   = $this->get('core.session.applicant')->getUser();
        $Leads      = $User->getUserLeads();
        foreach ($Leads as $Lead) {
            $isExist = $this->
                getDoctrine()->
                getRepository('DataBundle:Tradeline')->
                findOneBy(
                    array(
                        'cj_group_id' => $Lead->getCjGroupId(),
                        'tradeline' => $oApplicantTradeline->getTradeline()
                        )
                );
            if (empty($isExist)) {
                $em = $this->getDoctrine()->getManager();
                $tradeline = new Tradeline();
                $tradeline->setCjGroupId($Lead->getCjGroupId());
                $tradeline->setUser($Lead->getUser());
                $tradeline->setTradeline($oApplicantTradeline->getTradeline());
                $tradeline->setStatus($oApplicantTradeline->getStatus());
                $em->persist($tradeline);
                $em->flush();
            }
        }
    }
}
