<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LandlordController extends Controller
{
    public function getUser()
    {
        if ($user = parent::getUser()) {
            $user = $this->get('core.session.landlord')->getUser();
            return $user;
        }
    }

    public function getGroups()
    {
        $user = $this->getUser();
        return $user->getGroups();
    }

    public function getCurrentGroup()
    {
        return $this->get('core.session.landlord')->getGroup();
    }
}
