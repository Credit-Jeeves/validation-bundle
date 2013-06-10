<?php
namespace CreditJeeves\AdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use CreditJeeves\DataBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/")
 */
class ApplicantAdminController extends Controller
{
    /**
     * @Route("/cj/applicant/{id}/observe", name="admin_cj_applicant_observe")
     * @Template()
     *
     * @return array
     */
    public function observeAction($id)
    {
    }
    /**
     * @Route("/cj/applicant/{id}/report", name="admin_cj_applicant_report")
     * @Template()
     *
     * @return array
     */
    public function reportAction($id)
    {
        
    }

}
