<?php
namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\AdminBundle\Controller\ApplicantController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TenantController extends ApplicantController
{
    /**
     * @Route("/rj/tenant/{id}/report", name="admin_user_new_report")
     */
    public function reportAction($id)
    {
        parent::reportAction($id);

        return new RedirectResponse(
            $this->generateUrl(
                'admin_cj_report_list',
                array(
                    'user_id' => $id
                )
            )
        );
    }
}
