<?php

namespace RentJeeves\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LandlordController extends BaseController
{
    /**
     * @param int $id
     * @param Request $request
     *
     * @Route("/landlord/{id}/unlock", name="admin_landlord_unlock")
     *
     * @return RedirectResponse
     */
    public function unlockAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $loginDefence = $em->getRepository('DataBundle:LoginDefense')->findOneBy(['user' => $id]);
        if (null !== $loginDefence) {
            $em->remove($loginDefence);
            $em->flush();
            $request->getSession()->getFlashBag()->add(
                'sonata_flash_success',
                'Landlord has been successfully unlocked.'
            );
        }

        return $this->redirect($this->generateUrl('admin_landlord_edit', ['id' => $id]));
    }
}
