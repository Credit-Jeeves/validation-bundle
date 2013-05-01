<?php

namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\ExperianBundle\Atb;
use CreditJeeves\ExperianBundle\AtbSimulation;
use CreditJeeves\ExperianBundle\Simulation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $this->getSimulation();
        return array();
        if (!$this->getRequest()->isMethod('POST')) {
            return $this->createNotFoundException('Method must be POST');
        }
        $result = array();
        $this->form = new cjApplicantSimulationForm();
        if ($this->getRequest()->get($this->form->getName())) {
            $result = $this->processForm();
        } elseif ($this->getRequest()->get('score')) {
            ignore_user_abort();
            set_time_limit(90);
            $atbSimulation = atbSimulationTable::getInstance()->increaseScoreByX(
                $this->getRequest()->getParameter('score'),
                $this->cjApplicant
            );
            if ($this->getRequest()->getParameter('save', false)) {
                $atbSimulation->save();
            }
            $result = $atbSimulation->getResultData();
        }

        return new JsonResponse($result);

    }

    /**
     * @return Simulation
     */
    protected function getSimulation()
    {
        /* @var $report Report */
        $report = $this->get('core.session.applicant')->getUser()->getReportsD2c()->last();
        /* @var $simulation AtbSimulation */
        $simulation = $this->get('experian.atb_simulation');
        $simulation->setReport($report);
        return $simulation;
    }

    protected function processForm()
    {
        $request = $this->getRequest();
        $this->form->bind($request->getParameter($this->form->getName()));
        if ($this->form->isValid()) {
            $input = $this->form->getValues();
            ignore_user_abort();
            set_time_limit(90);
            $atbSimulation = atbSimulationTable::getInstance()->bestUseOfCash(
                $input['best_use_of_cash'],
                $this->cjApplicant
            );
            if ($request->getParameter('save', false)) {
                $atbSimulation->save();
            }
            return $atbSimulation->getResultData();
        }

        return array('message' => 'Invalid');
    }

    /**
     * @Template()
     */
    public function simulationAction()
    {
        return array();
    }
}
