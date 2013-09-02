<?php
namespace RentJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/")
 */
class ObserveController extends Controller
{
    /**
     * @Route("/observe/applicant/{id}/{type}", name="admin_applicant_observe", defaults={"type" = "applicant"})
     * @Route("/observe/dealer/{id}/{type}", name="admin_dealer_observe", defaults={"type" = "dealer"})
     * @Route("/observe/landlord/{id}/{type}", name="admin_landlord_observe", defaults={"type" = "landlord"})
     * @Route("/observe/tenant/{id}/{type}", name="admin_tenant_observe", defaults={"type" = "tenant"})
     */
    public function indexAction($id, $type)
    {
        $this->get('session')->set('observe_admin_id', $this->getUser()->getId());
        $user = $this->get('doctrine.orm.entity_manager')->getRepository('DataBundle:User')->find($id);
        $this->get('security.context')->getToken()->setUser($user);
        $this->container->get('core.session.' . $type)->setUser($user);
        return $this->redirect($this->get('router')->generate($type . '_homepage'));
    }
}
