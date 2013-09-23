<?php
namespace CreditJeeves\PublicBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SendController extends Controller
{
    /**
     * @Route("/new/send", name="applicant_new_send")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $user = $this->get('core.session.applicant')->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }
        $code = $user->getInviteCode();
        if (empty($code)) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $this->get('project.mailer')->sendCheckEmail($user);
        }
        return array(
            'email' => $user->getEmail()
        );
    }
}
