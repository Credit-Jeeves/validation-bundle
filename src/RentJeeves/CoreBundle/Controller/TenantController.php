<?php
namespace RentJeeves\CoreBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\Score;
use RentJeeves\DataBundle\Entity\Tenant;

class TenantController extends BaseController
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
        $user_agent = strtolower ( $_SERVER['HTTP_USER_AGENT'] );
        if(preg_match ( "/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $user_agent )){
            return true;
        }else{
            return false;
        }
    }
}
