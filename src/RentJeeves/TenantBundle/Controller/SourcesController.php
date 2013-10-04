<?php

namespace RentJeeves\TenantBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 *
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 */
class SourcesController extends Controller
{
    /**
     * @Route("/sources", name="tenant_payment_sources")
     * @Template()
     */
    public function indexAction()
    {
        $paymentAccounts = $this->getUser()->getPaymentAccounts();
        return array(
            'paymentAccounts' => $paymentAccounts
        );
    }

    /**
     * @Route("/del/{id}", name="tenant_payment_sources_del", options={"expose"=true})
     */
    public function delAction($id)
    {
        return $this->redirect('');
    }
}
