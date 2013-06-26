<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TenantController extends Controller
{
    protected $report;

    public function getUser()
    {
        if ($user = parent::getUser()) {
            $this->getUserDetails($user);
            $user = $this->get('core.session.tenant')->getUser();
            return $user;
        }
    }
    public function getReport()
    {
        return $this->report;
    }

    private function getUserDetails($user)
    {
        $this->report = $user->getReportsPrequal()->last();
    }
}
