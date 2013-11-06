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
     *     "/management/{propertyId}",
     *     name="management_login",
     *      defaults={
     *          "propertyId"=null
     *      }
     * )
     * @Template()
     */
    public function indexAction($propertyId)
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
        $url = 'http://www.renttrack.com/';
        if (!empty($propertyId)) {
            $url = $this->generateUrl('iframe_new', array('propertyId' => $propertyId), true);
        }
        return array(
            'form' => $form->createView(),
            'url' => $url,
        );
    }
}
