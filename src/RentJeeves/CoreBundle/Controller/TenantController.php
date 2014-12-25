<?php
namespace RentJeeves\CoreBundle\Controller;

use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\Score;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TenantController extends Controller
{
    /**
     * @var Report
     */
    protected $report;

    /**
     * @var Score
     */
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

    private function getUserDetails(Tenant $user)
    {
        $this->report = $user->getReports()->last();
        $this->score = $user->getScores()->last();
    }

    public function isMobile()
    {
        return false;
    }
}
