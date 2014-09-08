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
    public function setPreciseIdApi($preciseIdApi)
    {
        parent::setPreciseIdApi($preciseIdApi);
    }

    /**
     * // Do not remove it!!!
     * @DI\InjectParams({
     *     "pidkiqQuestions" = @DI\Inject("experian.net_connect.precise_id.questions")
     * })
     */
    public function setPreciseIdQuestions($preciseIdQuestions)
    {
        parent::setPreciseIdQuestions($preciseIdQuestions);
    }

    protected function setupUserIsValidUserIntoSession(Request $request)
    {
        $session = $request->getSession();
        $session->set('isValidUser', $this->preciseIdQuestions->isValidUser());
    }

    /**
     * @Route("/get", name="experian_pidkiq_get", options={"expose"=true})
     * @Template()
     *
     * @return JsonResponse | array
     */
    public function getAction(Request $request)
    {
        if (!$this->preciseIdQuestions->processQuestions()) {
            $this->setupUserIsValidUserIntoSession($request);
            $response = array(
                'status'          => 'error',
                'error'           => $this->preciseIdQuestions->getError(),
                'isValidUser'     => $this->preciseIdQuestions->isValidUser(),
            );
            return new JsonResponse($response);
        }

        if ($questionsData = $this->preciseIdQuestions->getQuestionsData()) {
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
                    'isValidUser'       => $this->preciseIdQuestions->isValidUser(),
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
        if ($questions = $this->preciseIdQuestions->retrieveQuestions()) {
            $form = $this->createForm(new QuestionsType($questions));
        } else {
            $this->setupUserIsValidUserIntoSession($request);
            return new JsonResponse(
                array(
                    'status'          => 'error',
                    'error'           => $this->getErrorMessageQuestionNotFound(),
                    'isValidUser'     => $this->preciseIdQuestions->isValidUser(),
                )
            );
        }
        $this->setupUserIsValidUserIntoSession($request);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($this->preciseIdQuestions->processForm($form)) {
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
