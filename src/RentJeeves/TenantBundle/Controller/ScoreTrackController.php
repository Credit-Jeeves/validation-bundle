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
        return array(
            'paymentAccounts' => $this->getUser()->getPaymentAccounts()
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
