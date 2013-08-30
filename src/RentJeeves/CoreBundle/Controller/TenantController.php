<?php
namespace RentJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TenantController extends Controller
{
    protected $report;

    protected $score;

    public function getUser()
    {
        if ($user = parent::getUser()) {
            $user = $this->get('core.session.applicant')->getUser();
            $this->getUserDetails($user);
            return $user;
        }
    }

    public function getScore()
    {
        return $this->score;
    }

    public function getReport()
    {
        return $this->report;
    }

    private function getUserDetails($user)
    {
        $this->report = $user->getReportsPrequal()->last();
        $this->score = $user->getScores()->last();
    }
}
