<?php
namespace CreditJeeves\CoreBundle\Controller;

use CreditJeeves\CoreBundle\Experian\Pidkiq as PidkiqApi;
use CreditJeeves\DataBundle\Entity\Pidkiq;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @method User getUser()
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
     * @return Pidkiq
     */
    protected function getPidkiqApi()
    {
        /** @var $model Pidkiq */
        $model = $this->getUser()->getPidkiqs()->last();
        $checkSum = md5(serialize($this->getUser()->getArrayForPidkiq()));
        if ($model) {
            $currentDate = new \DateTime();
            $dateOfModel = new \DateTime($model->getUpdatedAt());
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
        $pidiqModel = $this->getPidkiqApi();

        if (!$pidiqModel->getQuestions()) {
            $pidiqModel->setCjApplicant($this->getUser());
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

            $questions = $this->pidkiqApi->getResponseOnUserData($this->account);

            $pidiqModel->setQuestions($questions);
            $pidiqModel->setSessionId($this->pidkiqApi->getSessionId());
            $pidiqModel->setCheckSumm(md5(serialize($this->getUser()->getArrayForPidkiq())));
            $em->persist($pidiqModel);
            $em->flush();
        }
        return $pidiqModel->getQuestions();
    }

    /**
     * @DI\InjectParams({
     *     "pidkiq" = @DI\Inject("core.experian.pidkiq")
     * })
     */
    public function setPidkiqApi(PidkiqApi $pidkiq)
    {
        $this->pidkiqApi = $pidkiq;
    }

    /**
     * @Route("/check", name="core_pidkiq")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $account = $this->getUser();
        if (empty($account)) {
            throw $this->createNotFoundException('Account does not found');
        }
        if (UserIsVerified::PASSED == $this->getUser()->getIsVerified()) {
            $this->redirect($this->generateUrl($this->routeHomepage));
        }

        $supportEmail = $this->container->getParameter('support_email');
        $supportEmailTag = "<a href=\"mailto:{$supportEmail}\">{$supportEmail}</a>";

        $i18n = $this->get('translator');

        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {
            try {
                try {
                    if (false === $this->retrieveQuestions()) {
                        $this->error = $i18n->trans(
                            'pidkiq.error.timeout-%SUPPORT_EMAIL%',
                            array('%SUPPORT_EMAIL%' => $supportEmailTag)
                        );
                    } else {
                        return $this->renderText(json_encode('finished'));
                    }
                } catch (ExperianException $e) {
                    if (!Server::isTestEnv()) {
                        fpErrorNotifier::getInstance()->handler()->handleException($e);
                    }
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
//                fpErrorNotifier::getInstance()->handler()->handleException($e); // FIXME
                return $this->renderText(json_encode('error'));
            }
        } elseif ($this->questionsData = $this->getPidkiqApi()->getQuestions()) {
            $this->form = new cjApplicantQuestionsForm($this->questionsData);
            if ($request->isMethod(sfRequest::POST) && $request->hasParameter($this->form->getName())) {
                $this->processForm();
            }
        }

        if (!empty($this->error)) {
            $this->getSession()->setFlash('message_title', $i18n->trans('pidkiq.title'));
            $this->getSession()->setFlash('message_body', $this->error);
            if ($request->isXmlHttpRequest()) {
                return $this->renderText(json_encode(array('url' => $this->generateUrl('public_message_flash'))));
            } else {
                return $this->redirect($this->routeMessage);
            }
        }
    }

    /**
     * Process form
     */
    public function processForm()
    {
        $request = $this->getRequest();
        $this->form->bind($request->getParameter($this->form->getName()));
        if ($this->form->isValid()) {
            $pidkiq = Pidkiq::getInstance();
            if ($pidkiq->getResult(
                $this->account->getCjApplicantPidkiq()->getLast()->getSessionId(),
                $this->form->getValues()
            )) {
                $this->account->changeIsVerified(UserIsVerified::PASSED);
                return $this->redirect($this->routeHomepage);
            } else {
                if (UserIsVerified::NONE == $this->account->getIsVerified()) {
                    $this->account->changeIsVerified(UserIsVerified::FAILED);
                } else {
                    $this->account->changeIsVerified(UserIsVerified::LOCKED);
                }
                $this->error = $this->getContext()->getI18N()->__(
                    'pidkiq.error.answers-%SUPPORT_EMAIL%',
                    array('%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'))
                );
            }
        }
    }
}
