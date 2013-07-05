<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

class PropertyController extends Controller
{
    /**
     * @Route("/property/new", name="landlord_property_new")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route(
     *  "/property/add",
     *  name="landlord_property_add",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"},
     *  options={"expose"=true}
     * )
     * @Method({"POST"})
     *
     * @return array
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $address = $request->request->all('data');
        print_r(json_decode($address['data'], true));
        
        return new JsonResponse(array());
    }
}
