<?php
namespace RentJeeves\TenantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ScoreTrackController extends Controller
{
    /**
     * @Template()
     * @return array
     */
    public function payAction()
    {

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('DataBundle:Group')->findByCode('RentTrack')[0];

        return array(
          'paymentAccounts' => $this->getUser()->getPaymentAccounts(),
          'paymentGroup' => $group
        );
    }

    /**
     * @Template()
     * @return array
     */
    public function promoboxAction()
    {
        return array();
    }

    /**
     * @Template()
     * @return array
     */
    public function pricingAction()
    {
        return array();
    }
}
