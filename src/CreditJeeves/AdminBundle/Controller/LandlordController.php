<?php
namespace CreditJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LandlordController extends Controller
{
    /**
     * @Route("/cj/landlord/{id}/observe", name="admin_landlord_observe")
     */
    public function observeAction($id = null)
    {
      $user = $this->getDoctrine()->getRepository('DataBundle:User')->find($id);
      $this->get('core.session.landlord')->setUser($user);
      $url = $this->get('router')->generate('landlord_homepage');
      return new RedirectResponse($url);
    }
}
