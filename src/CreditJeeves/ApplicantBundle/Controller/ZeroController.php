<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


use Sonata\AdminBundle\Controller\CRUDController;
use Rj\EmailBundle\Swift\Message;
use FOS\RestBundle\Controller\Annotations\View;
use Rj\EmailBundle\Entity\EmailTemplate;

use CreditJeeves\CoreBundle\Mailer\Mailer;

/**
 * This page would be usefull for development
 * @author Alex
 * @Route("/")
 */
class ZeroController extends Controller
{
    /**
     * @Route("/zero", name="applicant_zero_page")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $sTemplate = '';
        $sEmail = '';
        $User    = $this->get('core.session.applicant')->getUser();
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $sTemplate = $request->request->get('template');
            $sEmail = $request->request->get('email');
            $sOldEmail = $User->getEmail();
            $User->setEmail($sEmail);
            $this->get('creditjeeves.mailer')->sendInviteToApplicant($User);
            $User->setEmail($sOldEmail);
        }
        return array(
            'sTemplate' => $sTemplate,
            'sEmail' => $sEmail,
            );
    }
}
