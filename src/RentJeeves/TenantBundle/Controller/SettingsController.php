<?php
namespace RentJeeves\TenantBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\DataBundle\Entity\Tenant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends Controller
{
    /**
     * @Route("/plans", name="user_plans")
     * @Template()
     */
    public function plansAction()
    {
        /** @var Tenant $user */
        $user = $this->getUser();

        $paymentPlan = $this->get('translator')->trans('settings.plans.free');

        if ($user->getSettings()->isCreditTrack()) {
            if ($paymentAccount = $user->getSettings()->getCreditTrackPaymentAccount()) {
                $paymentPlan = $this->get('translator')->trans(
                    'settings.plans.paid',
                    array('%PAYMENT_ACCOUNT%' => (string)$paymentAccount)
                );
            } else {
                $paymentPlan = $this->get('translator')->trans('settings.plans.none');
            }
        }
        return array(
            'paymentPlan' => $paymentPlan,
        );
    }
}
