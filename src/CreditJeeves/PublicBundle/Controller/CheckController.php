<?php

namespace CreditJeeves\PublicBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\LeadNewType;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Group;

class CheckController extends Controller
{
    /**
     * @Route("/new/check/{code}", name="applicant_new_check")
     * @Template()
     *
     * @return array
     */
    public function indexAction($code)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->findOneBy(array('invite_code' => $code));
        if (empty($user)) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }
        $em = $this->getDoctrine()->getManager();
        $leads = $user->getUserLeads();
        foreach ($leads as $lead) {
            $lead->setStatus(Lead::STATUS_ACTIVE);
            $em->flush();
        }
        $user->setInviteCode(null);
        $user->setIsActive(true);
        $em->flush();
        return array(
            'signinUrl' => $this->get('router')->generate('fos_user_security_login')
        );
    }
}
