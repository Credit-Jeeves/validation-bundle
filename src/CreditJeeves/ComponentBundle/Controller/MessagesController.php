<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MessagesController extends Controller
{
    /**
     * @Template()
     * @param \Report $Report
     */
    public function indexAction(Report $Report)
    {
        $aMessages = $Report->getApplicantMessages();
        return array(
                'aMessages' => $aMessages,
            );
    }
}
