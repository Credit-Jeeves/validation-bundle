<?php
namespace CreditJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Enum\UserType;

class ApplicantController extends Controller
{
    protected $report;
    protected $score;
    protected $target;

    public function getUser()
    {
        if ($user = parent::getUser()) {
            //@TODO it's hack for password change, becouse we use the same code for change passowrd
            // on the RentRack and CreditJeeves in future I think need change this code.
            $type = $user->getType();
            if ($type == UserType::LANDLORD || $type == UserType::TETNANT) {
                return $user;
            }
            $user = $this->get('core.session.applicant')->getUser();
            $this->getUserDetails($user);
            return $user;
        }
    }

    public function getReport()
    {
        return $this->report;
    }

    public function getTarget()
    {
        return $this->getLead()->getTargetScore();
    }

    public function getScore()
    {
        return $this->score;
    }

    public function getLead()
    {
        return $this->get('core.session.applicant')->getLead();
    }

    public function setLeadId($nLeadId)
    {
        $this->get('core.session.applicant')->setLeadId($nLeadId);
    }

    private function getUserDetails($user)
    {
        $this->report = $user->getReportsPrequal()->last();
        $this->score = $user->getScores()->last();
    }
}
