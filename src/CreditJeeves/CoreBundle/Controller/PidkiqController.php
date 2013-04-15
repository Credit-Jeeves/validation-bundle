<?php
namespace CreditJeeves\CoreBundle\Controller;

use CreditJeeves\DataBundle\Entity\Pidkiq;
use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @method User getUser()
 */
abstract class PidkiqController extends Controller
{
    /**
     * @var Pidkiq
     */
    protected $pidkiqApi;

    /**
     * @return Pidkiq
     */
    protected function getPidkiqApi()
    {
        /** @var $user User */
        $user = $this->getUser();
        /** @var $model Pidkiq */
        $model = $user->getPidkiqs()->last();
        $checkSum = md5(serialize($user->getArrayForPidkiq()));
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
     *     "netConnect" = @DI\Inject("core.experian.pidkiq")
     * })
     */
    public function setPidkiqApi(Pidkiq $pidkiq)
    {
        $this->pidkiqApi = $pidkiq;
    }

    /**
     * @var string
     */
    protected $error = '';

    /**
     * @var array
     */
    protected $questionsData = array();

    /**
     * @var string
     */
    protected $routeMessage = 'message';

    /**
     * @var string
     */
    protected $routeHomepage = '@homepage';

    /**
     * @var string
     */
    protected $selfRoute = '@check';

    /**
     * @Route("/check", name="applicant_pidkiq")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $this->account = $this->getUser();
        $this->forward404If(empty($this->account), 'Account does not found');
        $this->redirectIf(cjApplicantIsVerified::PASSED == $this->getUser()->getIsVerified(), $this->routeHomepage);

        $supportEmail = sfConfig::get('app_support_email');
        $supportEmail = "<a href=\"mailto:{$supportEmail}\">{$supportEmail}</a>";

        $i18n = $this->getContext()->getI18N();

        if ($request->isXmlHttpRequest()) {
            try {
                try {
                    if (false === $this->retrieveQuestions()) {
                        $this->error = $i18n->__(
                            'We are unable to contact Experian at this time due to connectivity issues. ' .
                                'Please email %SUPPORT_EMAIL% to let us know you were unable to authenticate with Experian.',
                            array('%SUPPORT_EMAIL%' => $supportEmail)
                        );
                    } else {
                        return $this->renderText(json_encode('finished'));
                    }
                } catch (ExperianException $e) {
                    if (!Server::isTestEnv()) fpErrorNotifier::getInstance()->handler()->handleException($e);
                    switch ($e->getCode()) {
                        case E_USER_ERROR:
                            $this->error = $i18n->__(
                                'You have attempted to authenticate too many times recently. Please try again in an hour.'
                            );
                            break;

                        case E_ERROR:
                            $this->error = $i18n->__(
                                'Connection Error. Please contact %SUPPORT_EMAIL%',
                                array('%SUPPORT_EMAIL%' => $supportEmail)
                            );
                            break;

                        default:
                        case E_NOTICE:
                            if ('Cannot formulate questions for this consumer.' == $e->getMessage()) {
                                $this->error = $i18n->__(
                                    'We could not find your profile at Experian. ' .
                                        'Please contact %SUPPORT_EMAIL% if you feel this is an error.',
                                    array('%SUPPORT_EMAIL%' => $supportEmail)
                                );
                                break;
                            }
                            $this->error = $i18n->__(
                                "We encountered an error when contacting Experian: '%ERROR%'. Please contact %SUPPORT_EMAIL%.",
                                array(
                                    '%SUPPORT_EMAIL%' => $supportEmail,
                                    '%ERROR%' => $e->getMessage()
                                )
                            );
                            break;

                    }
                }
            } catch (Exception $e) {
                fpErrorNotifier::getInstance()->handler()->handleException($e);
                return $this->renderText(json_encode('error'));
            }
        } elseif ($this->questionsData = $this->getPidkiqApi()->getQuestions()) {
            $this->form = new cjApplicantQuestionsForm($this->questionsData);
            if ($request->isMethod(sfRequest::POST) && $request->hasParameter($this->form->getName())) {
                $this->processForm();
            }
        }

        if (!empty($this->error)) {
            $this->getUser()->setFlash('message_title', $i18n->__('Identity Verification'));
            $this->getUser()->setFlash('message_body', $this->error);
            if ($request->isXmlHttpRequest()) {
                return $this->renderText(json_encode(array('url' => $this->generateUrl('message'))));
            } else {
                return $this->redirect($this->routeMessage);
            }
        }
        $this->setTemplate('check');
        return sfView::SUCCESS;
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
                $this->account->changeIsVerified(cjApplicantIsVerified::PASSED);
                return $this->redirect($this->routeHomepage);
            } else {
                if (cjApplicantIsVerified::NONE == $this->account->getIsVerified()) {
                    $this->account->changeIsVerified(cjApplicantIsVerified::FAILED);
                } else {
                    $this->account->changeIsVerified(cjApplicantIsVerified::LOCKED);
                }
                $this->error = $this->getContext()->getI18N()->__(
                    "Some of the answers you provided to the questions are incorrect. " .
                        "We're sorry but we cannot authenticate you at this time. " .
                        "Please try again in one hour, or contact %SUPPORT_EMAIL% if you feel this is an error.",
                    array('%SUPPORT_EMAIL%' => sfConfig::get('app_support_email'))
                );
            }
        }
    }
}
