<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class IndexController extends Controller
{
    /**
     * @Route("/", name="landlord_homepage")
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getGroups();
        if (null === $currentGroup = $this->getCurrentGroup()) {
            throw new \LogicException(sprintf('Group not found for Landlord#%d', $this->getUser()->getId()));
        }

        return [
            'nGroups' => $groups->count(),
            'Group' => $currentGroup,
        ];
    }

    /**
     * @Route(
     *  "/pending-invites/",
     * )
     *
     */
    public function allInvitesAction()
    {
        $sHost = $this->container->getParameter('server_name');
        $group = $this->getCurrentGroup();
        $tenants = $this->getDoctrine()->getRepository('RjDataBundle:Tenant')->getTenantsPage($group);

        /** @var Tenant $tenant */
        $t = [];
        foreach ($tenants as $tenant) {
            $name = $tenant->getFullName();
            $email = $tenant -> getEmail();
            $item = $tenant->getInviteCode();
            $str = "<td>".$name."</td><td>".$email."</td>";
            $str .= "<td><a href ='http://".$sHost."/tenant/invite/".$item."'>Invitation Link</a></td>";
            array_push($t, $str);
        }
        return $this->render(
            'LandlordBundle:Index:pending-invites.html.twig',
            ['invites' => $t]
        );
    }
}
