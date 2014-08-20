<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\PublicBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use RentJeeves\PublicBundle\Form\LoginType;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\HttpFoundation\RedirectResponse;

class IframeController extends Controller
{
    /**
     * @Route(
     *      "/management/{id}/{type}",
     *      name="management_login",
     *      defaults={
     *          "id"=null,
     *          "type"="property"
     *      }
     * )
     * @Template()
     */
    public function indexAction($id, $type)
    {
        $form = $this->createForm(
            new LoginType(),
            null,
            array(
                'action' => $this->generateUrl('fos_user_security_check'),
                'attr' => array(
                    'target' => '_blank'
                 )
            )
        );
        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');
        $form->get('_csrf_token')->setData($csrfToken);

        return array(
            'form' => $form->createView(),
            'id'   => $id,
            'type' => $type,
        );
    }
}
