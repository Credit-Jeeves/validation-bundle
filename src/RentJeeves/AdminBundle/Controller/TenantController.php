<?php
namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\AdminBundle\Controller\ApplicantController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @param int $id
     * @param Request $request
     *
     * @Route("/rj/tenant/{id}/unlock", name="admin_tenant_unlock")
     *
     * @return RedirectResponse
     */
    public function unlockAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $loginDefence = $em->getRepository('DataBundle:LoginDefense')->findOneBy(['user' => $id]);

        if ($loginDefence) {
            $em->remove($loginDefence);
            $em->flush();

            $request->getSession()->getFlashBag()->add('sonata_flash_success', 'Tenant was unlocked successfully.');
        }

        return $this->redirect($this->generateUrl('admin_tenant_edit', ['id' => $id]));
    }
}
