<?php

namespace RentJeeves\CheckoutBundle\Controller;

use Payum\Request\CaptureRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ComponentController extends Controller
{
    /**
     * @Template()
     */
    public function payAction()
    {
        return array();
    }
}
