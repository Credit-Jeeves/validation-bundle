<?php
namespace RentJeeves\ExperianBundle\Controller;

use CreditJeeves\ExperianBundle\Controller\PidkiqController as Base;
use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use CreditJeeves\ExperianBundle\Services\PidkiqQuestions;
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
     *     "pidkiqApi" = @DI\Inject("experian.net_connect.precise_id")
     * })
     */
    public function setPidkiqApi($pidkiqApi)
    {
        parent::setPidkiqApi($pidkiqApi);
    }

    /**
     * // Do not remove it!!!
     * @DI\InjectParams({
     *     "pidkiqQuestions" = @DI\Inject("pidkiq.questions")
     * })
     */
    public function setPidkiqQuestions($pidkiqQuestions)
    {
        parent::setPidkiqQuestions($pidkiqQuestions);
    }

    protected function setupUserIsValidUserIntoSession(Request $request)
    {
        $session = $request->getSession();
        $session->set('isValidUser', $this->pidkiqQuestions->isValidUser());
    }

    /**
     * @Route("/get", name="experian_pidkiq_get", options={"expose"=true})
     * @Template()
     *
     * @return JsonResponse | array
     */
    public function getAction(Request $request)
    {
        if (!$this->pidkiqQuestions->processQuestions()) {
            $this->setupUserIsValidUserIntoSession($request);
            $response = array(
                'status'          => 'error',
                'error'           => $this->pidkiqQuestions->getError(),
                'isValidUser'     => $this->pidkiqQuestions->isValidUser(),
            );
            return new JsonResponse($response);
        }

        if ($questionsData = $this->pidkiqQuestions->getQuestionsData()) {
            $form = $this->createForm(new QuestionsType($questionsData));
            return array(
                'status'    => 'ok',
                'form'      => $form->createView()
            );
        } else {
            return new JsonResponse(
                array(
                    'status'            => 'error',
                    'error'             => $this->getErrorMessageQuestionNotFound(),
                    'isValidUser'       => $this->pidkiqQuestions->isValidUser(),
                )
            );
        }
    }

    /**
     * @Route("/execute", name="experian_pidkiq_execute", options={"expose"=true})
     * @Method({"POST"})
     */
    public function executeAction(Request $request)
    {
        if ($questions = $this->pidkiqQuestions->retrieveQuestions()) {
            $form = $this->createForm(new QuestionsType($questions));
        } else {
            $this->setupUserIsValidUserIntoSession($request);
            return new JsonResponse(
                array(
                    'status'          => 'error',
                    'error'           => $this->getErrorMessageQuestionNotFound(),
                    'isValidUser'     => $this->pidkiqQuestions->isValidUser(),
                )
            );
        }
        $this->setupUserIsValidUserIntoSession($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($this->pidkiqQuestions->processForm($form)) {
                return new JsonResponse(
                    array(
                        'success' => true,
                        'verification' => $this->getUser()->getIsVerified()
                    )
                );
            }

            //Setup not valid answer
            $response = array(
                $form->getName() => array(
                    '_globals' => array(
                        $this->get('translator')->trans(
                            'pidkiq.error.incorrect.answer-%SUPPORT_EMAIL%',
                            array(
                                '%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'),
                                '%MAIN_LINK%'     => $this->container->getParameter('external_urls')['user_voice'],
                            )
                        )
                    )
                )
            );
            return new JsonResponse($response);
        } else {
            return $this->renderErrors($form);
        }
    }

    protected function getErrorMessageQuestionNotFound()
    {
        $supportEmail = $this->container->getParameter('support_email');
        $externalUrls = $this->container->getParameter('external_urls');
        $userVoice   = $externalUrls['user_voice'];

        return $this->get('translator')->trans(
            'pidkiq.error.questions-%SUPPORT_EMAIL%',
            array(
                '%SUPPORT_EMAIL%' => $supportEmail,
                '%MAIN_LINK%'     => $userVoice,
            )
        );
    }
}
