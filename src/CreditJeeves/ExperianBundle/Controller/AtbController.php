<?php

namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\ExperianBundle\Atb;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/atb")
 */
class AtbController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $report Report */
        $report = $this->get('core.session.applicant')->getUser()->getReportsD2c()->last();
        /* @var $atb Atb */
        $atb = new Atb(
            $report->getArfParser(),
            $this->container->getParameter('experian.atb'),
            $this->get('fp_badaboom.exception_catcher')
        );

        return array();
    }
}
