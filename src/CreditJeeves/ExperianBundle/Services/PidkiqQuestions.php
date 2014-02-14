<?php

namespace CreditJeeves\ExperianBundle\Services;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\ExperianBundle\Pidkiq as ServicePidkiq;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use \DateTime;
use CreditJeeves\DataBundle\Entity\Pidkiq;
use \Exception;
use \ExperianException;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("pidkiq.questions")
 */
class PidkiqQuestions
{
    /**
     * @var bool
     */
    protected $isValidUser = false;

    /**
     * @var ServicePidkiq
     */
    protected $pidkiqApi;

    /**
     * @var string
     */
    protected $error = '';

    /**
     * @var Translation Service
     */
    protected $translator;

    protected $options = array();

    protected $em;

    protected $securityContext;

    protected $catcher;

    protected $questionsData = array();

    /**
     * @InjectParams({
     *     "pidkiqApi"          = @Inject("experian.pidkiq"),
     *     "securityContext"    = @Inject("security.context"),
     *     "catcher"            = @Inject("fp_badaboom.exception_catcher"),
     *     "translator"         = @Inject("translator"),
     *     "supportEmail"       = @Inject("%support_email%"),
     *     "externalUrls"       = @Inject("%external_urls%"),
     *     "em"                 = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(
        $pidkiqApi,
        $securityContext,
        $catcher,
        $translator,
        $supportEmail,
        $externalUrls,
        $em
    ) {
        $this->pidkiqApi = $pidkiqApi;
        $this->securityContext = $securityContext;
        $this->catcher = $catcher;
        $this->translator = $translator;
        $this->options['support_email'] = $supportEmail;
        $this->options['external_urls'] = $externalUrls;
        $this->em = $em;
    }

    public function isValidUser()
    {
        return $this->isValidUser;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getQuestionsData()
    {
        return $this->questionsData;
    }

    private function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    protected function checkSumm()
    {
        return md5(serialize($this->getUser()->getArrayForPidkiq()));
    }

    /**
     * @return Pidkiq
     */
    protected function getPidkiqModel()
    {
        /** @var $model Pidkiq */
        $model = $this->getUser()->getPidkiqs()->last();
        $checkSum = $this->checkSumm();
        if ($model) {
            $currentDate = new DateTime();
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
    public function retrieveQuestions()
    {
        $pidiqModel = $this->getPidkiqModel();

        if (!$pidiqModel->getQuestions()) {
            $pidiqModel->setUser($this->getUser());
            if (2 < ($try = $pidiqModel->getTryNum())) {
                $pidiqModel->setTryNum(0);
                $this->em->persist($pidiqModel);
                $this->em->flush();
                return false;
            }
            $pidiqModel->setTryNum($try + 1);
            $this->em->persist($pidiqModel);
            $this->em->flush();

            $this->pidkiqApi->execute();
            $questions = $this->pidkiqApi->getResponseOnUserData($this->getUser());

            $pidiqModel->setQuestions($questions);
            $pidiqModel->setSessionId($this->pidkiqApi->getSessionId());
            $pidiqModel->setCheckSumm($this->checkSumm());
            $this->em->persist($pidiqModel);
            $this->em->flush();
        }

        $this->isValidUser = true;
        return $this->questionsData = $pidiqModel->getQuestions();
    }

    public function processQuestions()
    {
        $supportEmail = $this->options['support_email'];
        $externalUrls = $this->options['external_urls'];
        $userVoice   = $externalUrls['user_voice'];

        try {
            try {
                if (false === $this->retrieveQuestions()) {
                    $this->error = $this->translator->trans(
                        'pidkiq.error.timeout-%SUPPORT_EMAIL%',
                        array(
                            '%SUPPORT_EMAIL%' => $supportEmail
                        )
                    );
                } else {
                    return true;
                }
            } catch (ExperianException $e) {
                $this->catcher->handleException($e);
                switch ($e->getCode()) {
                    case E_USER_ERROR:
                        $this->error = $this->translator->trans('pidkiq.error.attempts');
                        break;
                    case E_ERROR:
                        $this->error = $this->translator->trans(
                            'pidkiq.error.connection-%SUPPORT_EMAIL%',
                            array(
                                '%SUPPORT_EMAIL%' => $supportEmail
                            )
                        );
                        break;
                    case E_ALL:
                        $this->error = $this->translator->trans(
                            'pidkiq.error.could.not.find.profile-%SUPPORT_EMAIL%',
                            array(
                                '%SUPPORT_EMAIL%' => $supportEmail,
                                '%MAIN_LINK%'     => $userVoice,
                            )
                        );
                        break;
                    case E_NOTICE:
                    default:
                        if ('Cannot formulate questions for this consumer.' == $e->getMessage()) {
                            $this->isValidUser = true;
                            $this->error = $this->translator->trans(
                                'pidkiq.error.questions-%SUPPORT_EMAIL%',
                                array(
                                    '%SUPPORT_EMAIL%' => $supportEmail,
                                    '%MAIN_LINK%'     => $userVoice,
                                )
                            );
                            break;
                        }
                        $this->error = $this->translator->trans(
                            "pidkiq.error.generic-%SUPPORT_EMAIL%",
                            array(
                                '%SUPPORT_EMAIL%' => $supportEmail,
                                '%ERROR%' => $e->getMessage()
                            )
                        );
                        break;
                }
            }
        } catch (Exception $e) {
            $this->isValidUser = false;
            $this->catcher->handleException($e);
            $this->error = $e->getMessage();
        }
        return false;
    }

    /**
     * Process form
     */
    public function processForm($form)
    {
        $this->pidkiqApi->execute();
        if ($this->pidkiqApi->getResult(
            $this->getUser()->getPidkiqs()->last()->getSessionId(),
            $form->getData()
        )) {
            $this->getUser()->setIsVerified(UserIsVerified::PASSED);
            $this->em->persist($this->getUser());
            $this->em->flush();
            return true;
        }

        if (UserIsVerified::NONE == $this->getUser()->getIsVerified()) {
            $this->getUser()->setIsVerified(UserIsVerified::FAILED);
        } else {
            $this->getUser()->setIsVerified(UserIsVerified::LOCKED);
        }
        $this->em->persist($this->getUser());
        $this->em->flush();

        return false;
    }
}
