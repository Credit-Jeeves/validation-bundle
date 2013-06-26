<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TenantController extends Controller
{
    public function getUser()
    {
        if ($user = parent::getUser()) {
            $user = $this->get('core.session.tenant')->getUser();
            return $user;
        }
    }
}
