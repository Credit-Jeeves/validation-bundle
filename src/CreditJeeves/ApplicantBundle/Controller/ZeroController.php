<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\ApplicantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        return array();
    }
}
