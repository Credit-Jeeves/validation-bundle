<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor;

use CreditJeeves\DataBundle\Entity\Pidkiq as PidkiqModel;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\PidkiqStatus;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\PidKiqRuntimeException;
use Symfony\Component\Security\Core\SecurityContextInterface as SecurityContext;

abstract class PidKiqBaseProcessor implements PidKiqProcessorInterface, PidKiqStateAwareInterface
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
     * @var PidkiqModel
     */
    protected $pidkiqModel;

    /**
     * @var bool
     */
    protected $isSuccessfull = true;

    /**
     * @var PidKiqMessageGenerator
     */
    protected $messageGenerator;

    /**
     * @param SecurityContext $securityContext
     * @param EntityManager $em
     * @param PidKiqMessageGenerator $messageGenerator
     */
    public function __construct(
        SecurityContext $securityContext,
        EntityManager $em,
        PidKiqMessageGenerator $messageGenerator
    ) {
        $this->securityContext = $securityContext;
        $this->em = $em;
        $this->messageGenerator = $messageGenerator;
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

            if ($model) {
                if (PidkiqStatus::LOCKED === $model->getStatus()) {
                    $this->setIsSuccessfull(false);

                    return $this->pidkiqModel = $model;
                }

                if ((PidkiqStatus::BACKOFF === $model->getStatus() || PidkiqStatus::FAILURE === $model->getStatus()) &&
                    2 < $model->getTryNum()
                ) {
                    $this->setIsSuccessfull(false);
                    $model->setStatus(PidkiqStatus::LOCKED);
                    $this->em->persist($model);
                    $this->em->flush();

                    return $this->pidkiqModel = $model;
                }

                $currentDate = new \DateTime();
                $createdAt = clone $model->getCreatedAt();

                // If the last attemt of verification has status FAILURE, the next attempt should be in 1 hour.
                if (PidkiqStatus::FAILURE === $model->getStatus() && $createdAt->modify('+1 hour') > $currentDate) {
                    $this->setIsSuccessfull(false);
                    $model->setStatus(PidkiqStatus::BACKOFF);
                    $this->em->persist($model);
                    $this->em->flush();

                    return $this->pidkiqModel = $model;
                }

                $updatedAt = clone $model->getUpdatedAt();
                $checkSum = $this->getPidkiqCheckSum();

                if (!$model->getCheckSum() ||
                    ($updatedAt->modify('+5 minutes') >= $currentDate && $model->getCheckSum() == $checkSum)
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
        $questions = [];

        if ($this->getUser()->getIsVerified() === UserIsVerified::LOCKED) {
            $this->getPidkiqModel()->setStatus(PidkiqStatus::LOCKED);
            $this->setIsSuccessfull(false);
        } elseif ($this->getUser()->getIsVerified() === UserIsVerified::PASSED) {
            $this->getPidkiqModel()->setStatus(PidkiqStatus::SUCCESS);
        } else {
            $questions = $this->internalRetrieveQuestions();

            if (empty($questions) && PidkiqStatus::SUCCESS !== $this->getPidkiqModel()->getStatus()) {
                if (PidkiqStatus::LOCKED === $this->getPidkiqModel()->getStatus()) {
                    $this->getUser()->setIsVerified(UserIsVerified::LOCKED);
                }
                $this->setIsSuccessfull(false);
            }
        }

        $this->getPidkiqModel()->setTryNum($this->getPidkiqModel()->getTryNum() + 1);
        $this->getPidkiqModel()->setQuestions($questions);
        $this->getPidkiqModel()->setCheckSumm($this->getPidkiqCheckSum());
        $this->getPidkiqModel()->setUser($this->getUser());
        $this->em->persist($this->getPidkiqModel());
        $this->em->persist($this->getUser());
        $this->em->flush();

        return $questions;
    }

    /**
     * {@inheritdoc}
     */
    public function processAnswers(array $answers)
    {
        if (UserIsVerified::LOCKED === $this->getUser()->getIsVerified() ||
            (PidkiqStatus::INPROGRESS !== $this->getPidkiqModel()->getStatus() &&
            PidkiqStatus::FAILURE !== $this->getPidkiqModel()->getStatus())
        ) {
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
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->messageGenerator->generateMessage(
            $this->getPidkiqModel()->getStatus()
        );
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
     * @throws PidkiqRuntimeException
     */
    protected function getUser()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        if (($token = $this->securityContext->getToken()) &&
            is_object($user = $token->getUser()) &&
            $user instanceof User) {
            return $this->user = $user;
        }

        throw new PidKiqRuntimeException('Cannot get user for verification process.');
    }

    /**
     * @return string
     */
    protected function getPidkiqCheckSum()
    {
        return md5(serialize($this->getUser()->getArrayForPidkiq()));
    }

    /**
     * @return array
     */
    abstract protected function internalRetrieveQuestions();

    /**
     * @param array $answers
     * @return bool
     */
    abstract protected function internalProcessAnswers(array $answers);
}
