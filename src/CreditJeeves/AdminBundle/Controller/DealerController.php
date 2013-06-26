<?php
namespace CreditJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DealerController extends Controller
{
    /**
     * @Route("/cj/dealer/{id}/observe", name="admin_dealer_observe")
     */
    public function observeAction($id = null)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->find($id);
        $this->get('core.session.dealer')->setUser($user);
        $url = $this->get('router')->generate('dealer_homepage');
        return new RedirectResponse($url);
    }
}
