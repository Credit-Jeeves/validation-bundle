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
}
