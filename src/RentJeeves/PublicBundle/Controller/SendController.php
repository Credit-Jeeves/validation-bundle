<?php
namespace RentJeeves\PublicBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SendController extends Controller
{
    /**
     * @Route("/new/send/{tenantId}", name="user_new_send")
     * @Template()
     *
     * @return array
     */
    public function indexAction($tenantId)
    {
        $em = $this->getDoctrine()->getManager();
        $tenant = $em->getRepository('DataBundle:Tenant')->find($tenantId);

        $request = $this->get('request');
        $active = (is_null($tenant->getInviteCode())) ? TRUE : FALSE;

        if ($request->getMethod() == 'POST' && $tenant->getInviteCode()) {
            $this->get('creditjeeves.mailer')->sendRjCheckEmail($tenant);
        }

        return array(
            'tenantId' => $tenantId,
            'active'   => $active,
        );
    }
}
