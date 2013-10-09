<?php

namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\SimulationBundle\Enum\AtbType;
use CreditJeeves\SimulationBundle\Atb;
use CreditJeeves\SimulationBundle\AtbSimulation as Simulation;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/atb")
 */
class AtbController extends Controller
{
    /**
     * @Route("/simulate", name="experian_atb_simulate", options={"expose"=true})
     * @Template()
     */
    public function simulateAction()
    {
        if (!$this->getRequest()->isMethod('POST')) {
            throw $this->createNotFoundException('Method must be POST');
        }
        ignore_user_abort();
        set_time_limit(90);


        /* @var $report Report */
        $report = $this->getUser()->getReportsPrequal()->last();
        /** @var $lead Lead */
        $lead = $this->get('core.session.applicant')->getLead();

        /** @var $simulation Simulation */
        $simulation = $this->get('experian.atb_simulation');

        if ($money = $this->getRequest()->get('money')) {
            $result = $simulation->simulate(AtbType::CASH, $money, $report, $lead->getTargetScore());
        } else/*if ($this->getRequest()->get('score'))*/ {
            $result = $simulation->simulate(AtbType::SCORE, null, $report, $lead->getTargetScore());
        }

        $response = new Response($this->get('jms_serializer')->serialize($result, 'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Template()
     */
    public function simulationAction()
    {
        /* @var $report Report */
        $report = $this->get('core.session.applicant')->getUser()->getReportsPrequal()->last();

        /** @var $lead Lead */
        $lead = $this->get('core.session.applicant')->getLead();

        /** @var $simulation Simulation */
        $simulation = $this->get('experian.atb_simulation');

        $data = $simulation->getLastSimulation($report, $lead->getTargetScore());

        return array(
            'data' => $data
        );
    }
}
