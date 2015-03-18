<?php
namespace CreditJeeves\CheckoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/checkout", name="checkout_default")
     * @Route("/tenant/checkout", name="checkout_tenant")
     * @Template()
     */
    public function indexAction(Request $request)
    {
    }
}
