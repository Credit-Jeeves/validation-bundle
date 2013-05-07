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
        $User    = $this->get('core.session.applicant')->getUser();
        $User->setEmail('alex.emelyanov.ua@gmail.com');
        if ($this->get('creditjeeves.mailer')->sendInviteToApplicant($User)) {
            echo 'Ok';
        }
        return array();
    }
}
