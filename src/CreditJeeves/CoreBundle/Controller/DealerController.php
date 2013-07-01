<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DealerController extends Controller
{
    public function getUser()
    {
        if ($user = parent::getUser()) {
            $user = $this->get('core.session.dealer')->getUser();
            return $user;
        }
    }
}
