<?php
namespace CreditJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/")
 */
class TenantController extends Controller
{
    /**
     * @Route("/rj/tenant/{id}/observe", name="admin_tenant_observe")
     */
    public function observeAction($id = null)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->find($id);
        $this->get('core.session.tenant')->setUser($user);
        $url = $this->get('router')->generate('tenant_homepage');
        return new RedirectResponse($url);
    }
}
