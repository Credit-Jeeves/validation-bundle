<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor;

use CreditJeeves\DataBundle\Entity\Pidkiq as PidkiqModel;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\PidkiqStatus;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\RuntimeException;
use Symfony\Component\Security\Core\SecurityContextInterface as SecurityContext;
use Symfony\Component\Translation\Translator;

abstract class PidKiqProcessorBase implements PidKiqProcessorInterface, PidKiqStateAwareInterface
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var string
     */
    protected $supportEmail;

    /**
     * @var string
     */
    protected $supportUrl;

    /**
     * @var PidkiqModel
     */
    protected $pidkiqModel;

    /**
     * @var bool
     */
    protected $isSuccessfull = true;

    /**
     * @param SecurityContext $securityContext
     * @param EntityManager $em
     * @param Translator $translator
     * @param string $supportEmail
     * @param array $externalUrls
     */
    public function __construct(
        SecurityContext $securityContext,
        EntityManager $em,
        Translator $translator,
        $supportEmail,
        $externalUrls
    ) {
        $this->securityContext = $securityContext;
        $this->em = $em;
        $this->translator = $translator;
        $this->supportEmail = $supportEmail;
        $this->supportUrl = $externalUrls['user_voice'];
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getPidkiqModel()
    {
        if (!$this->pidkiqModel) {
            /** @var $model PidkiqModel */
            $model = $this->getUser()->getPidkiqs()->last();
            $checkSum = $this->getPidkiqCheckSum();

            if ($model) {
                if (PidkiqStatus::FAILURE === $model->getStatus() && 2 == $model->getTryNum()) {
                    $this->setIsSuccessfull(false);
                    $model->setStatus(PidkiqStatus::LOCKED);
                    $this->em->persist($model);
                    $this->em->flush();

                    return $this->pidkiqModel = $model;
                }

                $currentDate = new \DateTime();
                $dateOfModel = clone $model->getUpdatedAt();
                $onFailureDate = clone $model->getCreatedAt();

                if (PidkiqStatus::FAILURE === $model->getStatus() && $onFailureDate->modify('+1 hour') > $currentDate) {
                    $this->setIsSuccessfull(false);

                    return $this->pidkiqModel = $model;
                } elseif(
                    PidkiqStatus::FAILURE !== $model->getStatus() &&
                    (!$model->getCheckSumm() ||
                        ($dateOfModel->modify('+60 minutes') >= $currentDate && $model->getCheckSumm() == $checkSum))
                ) {
                    return $this->pidkiqModel = $model;
                }
            }

            return $this->pidkiqModel = new PidkiqModel();
        }

        return $this->pidkiqModel;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveQuestions()
    {
        if ($this->getUser()->getIsVerified() === UserIsVerified::LOCKED) {
            $this->getPidkiqModel()->setStatus(PidkiqStatus::LOCKED);
            $this->getPidkiqModel()->setUser($this->getUser());
            $this->setIsSuccessfull(false);
            $this->em->persist($this->getPidkiqModel());
            $this->em->flush();

            return [];
        }

        $questions = $this->internalRetrieveQuestions();

        if (!empty($questions)) {
            return $questions;
        }

        if (PidkiqStatus::LOCKED === $this->getPidkiqModel() && !$this->getIsSuccessfull()) {
            $this->getUser()->setIsVerified(UserIsVerified::LOCKED);
        }

        $this->em->persist($this->getUser());
        $this->em->flush();

        return [];
    }

    /**
     * @return array
     */
    abstract protected function internalRetrieveQuestions();

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        if (PidkiqStatus::FAILURE === $this->getPidkiqModel()->getStatus()) {
            return $this->translator->trans(
                'pidkiq.error.incorrect.answer-%SUPPORT_EMAIL%',
                [
                    '%SUPPORT_EMAIL%' => $this->supportEmail,
                    '%MAIN_LINK%'     => $this->supportUrl,
                ]
            );
        } elseif (PidkiqStatus::LOCKED === $this->getPidkiqModel()->getStatus()) {
            return $this->translator->trans(
                'pidkiq.error.lock-%SUPPORT_EMAIL%',
                ['%SUPPORT_EMAIL%' => $this->supportEmail]
            );
        } elseif (PidkiqStatus::BACKOFF === $this->getPidkiqModel()->getStatus()) {
            return $this->translator->trans('pidkiq.error.attempts');
        } elseif (PidkiqStatus::UNABLE === $this->getPidkiqModel()->getStatus()) {
            return $this->translator->trans(
                'pidkiq.error.questions-%SUPPORT_EMAIL%',
                [
                    '%SUPPORT_EMAIL%' => $this->supportEmail,
                    '%MAIN_LINK%'     => $this->supportUrl,
                ]
            );
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSuccessfull()
    {
        return $this->isSuccessfull;
    }

    /**
     * @param bool $isSuccessfull
     */
    protected function setIsSuccessfull($isSuccessfull)
    {
        $this->isSuccessfull = !!$isSuccessfull;
    }

    /**
     * @return User
     * @throws RuntimeException
     */
    protected function getUser()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        if (
            ($token = $this->securityContext->getToken()) &&
            is_object($user = $token->getUser()) &&
            $user instanceof User
        ) {
            return $this->user = $user;
        }

        throw new RuntimeException('Cannot get user for verification process.');
    }

    /**
     * @return string
     */
    protected function getPidkiqCheckSum()
    {
        return md5(serialize($this->getUser()->getArrayForPidkiq()));
    }

    /**
     * {@inheritdoc}
     */
    public function processAnswers(array $answers)
    {
        if (UserIsVerified::LOCKED == $this->getUser()->getIsVerified()) {
            return false;
        }

        $result = false;

        if ($this->internalProcessAnswers($answers)) {
            $this->getPidkiqModel()->setStatus(PidkiqStatus::SUCCESS);
            $this->getUser()->setIsVerified(UserIsVerified::PASSED);
            $result = true;
        } elseif (UserIsVerified::NONE == $this->getUser()->getIsVerified()) {
            $this->getUser()->setIsVerified(UserIsVerified::FAILED);
            $this->getPidkiqModel()->setStatus(PidkiqStatus::FAILURE);
        } else {
            $this->getUser()->setIsVerified(UserIsVerified::LOCKED);
            $this->getPidkiqModel()->setStatus(PidkiqStatus::LOCKED);
        }

        $this->em->persist($this->getPidkiqModel());
        $this->em->persist($this->getUser());
        $this->em->flush();

        return $result;
    }

    /**
     * @param array $answers
     * @return bool
     */
    abstract protected function internalProcessAnswers(array $answers);
}
