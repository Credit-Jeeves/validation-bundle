<?php
namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use CreditJeeves\ExperianBundle\Pidkiq as PidkiqApi;
use CreditJeeves\DataBundle\Entity\Pidkiq;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\ExperianBundle\Services\PidkiqQuestions;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \ExperianException;
use \Exception;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @method \CreditJeeves\DataBundle\Entity\User getUser()
 *
 * @Route("/")
 */
class PidkiqController extends Controller
{
    /**
     * @var $pidkiqQuestions PidkiqQuestions
     */
    protected $pidkiqQuestions;

    /**
     * @var $pidkiqApi PidkiqApi
     */
    protected $pidkiqApi;

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * @DI\InjectParams({
     *     "pidkiqApi" = @DI\Inject("experian.pidkiq")
     * })
     */
    public function setPidkiqApi($pidkiqApi)
    {
        $this->pidkiqApi = $pidkiqApi;
    }

    /**
     * @DI\InjectParams({
     *     "pidkiqQuestions" = @DI\Inject("pidkiq.questions")
     * })
     */
    public function setPidkiqQuestions($pidkiqQuestions)
    {
        $this->pidkiqQuestions = $pidkiqQuestions;
    }

    /**
     * @Route("/check", name="core_pidkiq")
     * @Template()
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        if (empty($user)) {
            throw $this->createNotFoundException('Account does not found');
        }
        if (UserIsVerified::PASSED == $this->getUser()->getIsVerified()) {
            $this->redirect($this->generateUrl('applicant_homepage'));
        }

        if ($request->isXmlHttpRequest()) {
            if ($this->pidkiqQuestions->processQuestions()) {
                return new JsonResponse('finished');
            }
        } elseif ($this->pidkiqQuestions->getQuestionsData()) {
            $form = $this->createForm(new QuestionsType($this->pidkiqQuestions->getQuestionsData()));
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($this->pidkiqQuestions->processForm($form)) {
                    return $this->redirect($this->generateUrl('applicant_homepage'));
                }
                //Setup not valid answer
                $this->pidkiqQuestions->setError(
                    $this->get('translator')->trans(
                        'pidkiq.error.answers-%SUPPORT_EMAIL%',
                        array(
                            '%SUPPORT_EMAIL%' => $this->container->getParameter('support_email')
                        )
                    )
                );
            }
            $form = $this->form->createView();
        }

        $error = $this->pidkiqQuestions->getError();

        if (!empty($error)) {
            $this->getSession()->getFlashBag()->add(
                'message_title',
                $this->get('translator')->trans('pidkiq.title')
            );
            $this->getSession()->getFlashBag()->add('message_body', $error);
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(array('url' => $this->generateUrl('public_message_flash')));
            } else {
                return $this->redirect($this->generateUrl('public_message_flash'));
            }
        }

        return array(
            'form'     => $form,
            'url'      => $this->generateUrl('core_pidkiq'),
            'redirect' => null,
        );
    }
}
