<?php
namespace RentJeeves\ExperianBundle\Controller;

use CreditJeeves\ExperianBundle\Controller\PidkiqController as Base;
use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @method \CreditJeeves\DataBundle\Entity\Tenant getUser()
 *
 * @Route("/experian/pidkiq")
 */
class PidkiqController extends Base
{

    /**
     * // Do not remove it!!!
     * @DI\InjectParams({
     *     "pidkiqApi" = @DI\Inject("experian.pidkiq")
     * })
     */
    public function setPidkiqApi($pidkiqApi)
    {
        parent::setPidkiqApi($pidkiqApi);
    }

    /**
     * @Route("/get", name="experian_pidkiq_get", options={"expose"=true})
     * @Template()
     *
     * @return JsonResponse | array
     */
    public function getAction()
    {
        if (!$this->processQuestions()) {
            $response = array(
                'status' => 'error',
                'error' => $this->error
            );
            return new JsonResponse($response);
        }


        if ($this->questionsData = $this->getPidkiq()->getQuestions()) {
            $this->form = $this->createForm(new QuestionsType($this->questionsData));
            return array(
                'status' => 'ok',
                'form' => $this->form->createView()
            );
        } else {
            return new JsonResponse(array('status' => 'error', 'error' => 'Can not get questions'));
        }

    }

    /**
     * @Route("/execute", name="experian_pidkiq_execute", options={"expose"=true})
     * @Method({"POST"})
     */
    public function executeAction(Request $request)
    {
        $this->form = $this->createForm(new QuestionsType($this->getPidkiq()->getQuestions()));

        $this->form->handleRequest($request);
        if (!$this->form->isValid()) {
            return $this->renderErrors($this->form);
        }

        if ($this->processForm()) {
            return new JsonResponse(
                array(
                    'success' => true
                )
            );
        }
        $response = array(
            'status' => 'error',
            'error' => $this->error
        );
        return new JsonResponse($response);
    }
}
