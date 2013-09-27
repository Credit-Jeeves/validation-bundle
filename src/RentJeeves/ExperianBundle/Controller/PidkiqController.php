<?php
namespace RentJeeves\ExperianBundle\Controller;

use CreditJeeves\ExperianBundle\Controller\PidkiqController as Base;
use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 *
 * @Route("/experian/pidkiq")
 */
class PidkiqController extends Base
{
    use FormErrors;

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

        if ($this->questionsData) {
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
        if ($questions = $this->retrieveQuestions()) {
            $this->form = $this->createForm(new QuestionsType($questions));
        } else {
            return new JsonResponse(array('status' => 'error', 'error' => 'Can not get questions'));
        }

        $this->form->handleRequest($request);
        if ($this->form->isValid()) {
            if ($this->processForm()) {
                return new JsonResponse(
                    array(
                        'success' => true,
                        'verification' => $this->getUser()->getIsVerified()
                    )
                );
            }
            $response = array(
                $this->form->getName() => array('_globals' => array($this->error))
            );
            return new JsonResponse($response);
        } else {
            return $this->renderErrors($this->form);
        }
    }
}
