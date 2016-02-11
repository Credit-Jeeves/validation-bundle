<?php
namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use CreditJeeves\ExperianBundle\NetConnect\PreciseID as PreciseIDApi;
use CreditJeeves\DataBundle\Entity\Pidkiq;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\ExperianBundle\NetConnect\PreciseIDQuestions;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @var $preciseIdQuestions PreciseIDQuestions
     */
    protected $preciseIdQuestions;

    /**
     * @var $preciseIdApi PreciseIDApi
     */
    protected $preciseIdApi;

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * @DI\InjectParams({
     *     "preciseIdApi" = @DI\Inject("experian.net_connect.precise_id")
     * })
     */
    public function setPreciseIdApi($preciseIdApi)
    {
        $this->preciseIdApi = $preciseIdApi;
    }

    /**
     * @DI\InjectParams({
     *     "preciseIdQuestions" = @DI\Inject("experian.net_connect.precise_id.questions")
     * })
     */
    public function setPreciseIdQuestions($preciseIdQuestions)
    {
        $this->preciseIdQuestions = $preciseIdQuestions;
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
        $form = null;

        if (empty($user)) {
            throw $this->createNotFoundException('Account does not found');
        }
        if (UserIsVerified::PASSED == $this->getUser()->getIsVerified()) {
            $this->redirect($this->generateUrl('applicant_homepage'));
        }

        if ($request->isXmlHttpRequest()) {
            if ($this->preciseIdQuestions->processQuestions()) {
                return new JsonResponse('finished');
            }
        } elseif ($this->preciseIdQuestions->retrieveQuestions()) {
            $form = $this->createForm(new QuestionsType($this->preciseIdQuestions->getQuestionsData()));
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($this->preciseIdQuestions->processForm($form)) {
                    return $this->redirect($this->generateUrl('applicant_homepage'));
                }
                //Setup not valid answer
                $this->preciseIdQuestions->setError(
                    $this->get('translator')->trans(
                        'pidkiq.error.answers-%SUPPORT_EMAIL%',
                        [
                            '%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'),
                            '%LIFETIME_MINUTES%' => $this->container->getParameter('pidkiq.lifetime.minutes'),
                        ]
                    )
                );
            }
            $form = $form->createView();
        }

        $error = $this->preciseIdQuestions->getError();

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
            'form' => $form,
            'url' => $this->generateUrl('core_pidkiq'),
            'redirect' => null,
            'failedLink' => $this->generateUrl('errorLoadReport')
        );
    }

    /**
     * @Route("/error/load/report", name="errorLoadReport")
     * @Template()
     *
     * @return array
     */
    public function errorLoadReportAction()
    {
        return [];
    }
}
