<?php
namespace RentJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LandlordController extends Controller
{
    public function getUser()
    {
        if ($user = parent::getUser()) {
            return $this->get('core.session.landlord')->getUser();
        }
    }

    public function getGroups()
    {
        $user = $this->getUser();
        if ($isAdmin = $user->getIsSuperAdmin()) {
            $holding = $this->getUser()->getHolding();
            return $holding->getGroups() ? $holding->getGroups() : null;
        } else {
            return $user->getAgentGroups() ? $user->getAgentGroups() : null;
        }
    }

    public function getCurrentGroup()
    {
        return $this->get('core.session.landlord')->getGroup();
    }
}
