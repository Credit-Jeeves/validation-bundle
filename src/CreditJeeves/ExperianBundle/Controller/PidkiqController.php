<?php
namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use CreditJeeves\ExperianBundle\Pidkiq as PidkiqApi;
use CreditJeeves\DataBundle\Entity\Pidkiq;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
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
     * @var PidkiqApi
     */
    protected $pidkiqApi;

    /**
     * @var string
     */
    protected $error = '';

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * @var array
     */
    protected $questionsData = array();

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->container->get('session');
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
     * @return Pidkiq
     */
    protected function getPidkiq()
    {
        /** @var $model Pidkiq */
        $model = $this->getUser()->getPidkiqs()->last();
        $checkSum = md5(serialize($this->getUser()->getArrayForPidkiq()));
        if ($model) {
            $currentDate = new \DateTime();
            $dateOfModel = $model->getUpdatedAt();
            $dateOfModel->modify('+5 minutes');
            if (!$model->getCheckSumm() || ($dateOfModel >= $currentDate && $model->getCheckSumm() == $checkSum)) {
                return $model;
            }
        }
        return new Pidkiq();
    }

    /**
     * Retrieve questions from service or from DB cache
     *
     * @return array
     */
    protected function retrieveQuestions()
    {
        $pidiqModel = $this->getPidkiq();

        if (!$pidiqModel->getQuestions()) {
            $pidiqModel->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
            if (2 < ($try = $pidiqModel->getTryNum())) {
                $pidiqModel->setTryNum(0);
                $em->persist($pidiqModel);
                $em->flush();
                return false;
            }
            $pidiqModel->setTryNum($try + 1);
            $em->persist($pidiqModel);
            $em->flush();

            $this->pidkiqApi->execute($this->container);
            $questions = $this->pidkiqApi->getResponseOnUserData($this->getUser());

            $pidiqModel->setQuestions($questions);
            $pidiqModel->setSessionId($this->pidkiqApi->getSessionId());
            $pidiqModel->setCheckSumm(md5(serialize($this->getUser()->getArrayForPidkiq())));
            $em->persist($pidiqModel);
            $em->flush();
        }
        return $this->questionsData = $pidiqModel->getQuestions();
    }

    protected function processQuestions()
    {
        $i18n = $this->get('translator');
        $supportEmail = $this->container->getParameter('support_email');
        $supportEmailTag = "<a href=\"mailto:{$supportEmail}\">{$supportEmail}</a>";
        try {
            try {
                if (false === $this->retrieveQuestions()) {
                    $this->error = $i18n->trans(
                        'pidkiq.error.timeout-%SUPPORT_EMAIL%',
                        array('%SUPPORT_EMAIL%' => $supportEmailTag)
                    );
                } else {
                    return true;
                }
            } catch (ExperianException $e) {
                $this->get('fp_badaboom.exception_catcher')->handleException($e);
                switch ($e->getCode()) {
                    case E_USER_ERROR:
                        $this->error = $i18n->trans('pidkiq.error.attempts');
                        break;
                    case E_ERROR:
                        $this->error = $i18n->trans(
                            'pidkiq.error.connection-%SUPPORT_EMAIL%',
                            array('%SUPPORT_EMAIL%' => $supportEmailTag)
                        );
                        break;
                    default:
                    case E_NOTICE:
                        if ('Cannot formulate questions for this consumer.' == $e->getMessage()) {
                            $this->error = $i18n->trans(
                                'pidkiq.error.questions-%SUPPORT_EMAIL%',
                                array('%SUPPORT_EMAIL%' => $supportEmailTag)
                            );
                            break;
                        }
                        $this->error = $i18n->trans(
                            "pidkiq.error.generic-%SUPPORT_EMAIL%",
                            array(
                                '%SUPPORT_EMAIL%' => $supportEmailTag,
                                '%ERROR%' => $e->getMessage()
                            )
                        );
                        break;
                }
            }
        } catch (Exception $e) {
            $this->get('fp_badaboom.exception_catcher')->handleException($e);
        }
        return false;
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

        $i18n = $this->get('translator');

        if ($request->isXmlHttpRequest()) {
            if ($this->processQuestions()) {
                return new JsonResponse('finished');
            } else {
                return new JsonResponse('error');
            }

        } elseif ($this->questionsData = $this->getPidkiq()->getQuestions()) {
            $this->form = $this->createForm(new QuestionsType($this->questionsData));
            if ($request->isMethod('POST')) {
                $this->form->bind($request);
                if ($this->form->isValid()) {
                    if ($this->processForm()) {
                        return $this->redirect($this->generateUrl('applicant_homepage'));
                    }
                }
            }
            $this->form = $this->form->createView();
        }

        if (!empty($this->error)) {
            $this->getSession()->getFlashBag()->add('message_title', $i18n->trans('pidkiq.title'));
            $this->getSession()->getFlashBag()->add('message_body', $this->error);
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(array('url' => $this->generateUrl('public_message_flash')));
            } else {
                return $this->redirect($this->generateUrl('public_message_flash'));
            }
        }
        return array(
            'form' => $this->form,
            'url' => $this->generateUrl('core_pidkiq'),
            'redirect' => null//$this->getRequest()->headers->get('referer'),
        );
    }

    /**
     * Process form
     */
    public function processForm()
    {
        $this->pidkiqApi->execute($this->container);
        $em = $this->getDoctrine()->getManager();
        if ($this->pidkiqApi->getResult(
            $this->getUser()->getPidkiqs()->last()->getSessionId(),
            $this->form->getData()
        )) {
            $this->getUser()->setIsVerified(UserIsVerified::PASSED);
            $em->persist($this->getUser());
            $em->flush();
            return true;
        } else {
            if (UserIsVerified::NONE == $this->getUser()->getIsVerified()) {
                $this->getUser()->setIsVerified(UserIsVerified::FAILED);
            } else {
                $this->getUser()->setIsVerified(UserIsVerified::LOCKED);
            }
            $em->persist($this->getUser());
            $em->flush();
            $this->error = $this->get('translator')->trans(
                'pidkiq.error.answers-%SUPPORT_EMAIL%',
                array('%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'))
            );
        }
        return false;
    }
}
