<?php
namespace CreditJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApplicantController extends Controller
{
    /**
     * @Route("/cj/applicant/{id}/report", name="admin_applicant_report")
     */
    public function reportAction($id)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->find($id);
        if (!$user) {
            throw new Exception('User not found');
        }

        try {
            $netConnect = $this->get('experian.net_connect');
            $netConnect->execute($this->container);
            $netConnect->getResponseOnUserData($user);
        } catch (Exception $e) {
            throw new NotFoundHttpException("Can't get report:".$e->getMessage());
        }


        return new RedirectResponse(
            $this->generateUrl(
                'admin_cj_report_list',
                array(
                    'user_id' => $user->getId()
                )
            )
        );
    }
}
