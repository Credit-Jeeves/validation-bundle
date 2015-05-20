<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor;

use CreditJeeves\DataBundle\Enum\PidkiqStatus;
use CreditJeeves\ExperianBundle\Model\NetConnectRequest;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use CreditJeeves\ExperianBundle\Model\QuestionSet;

use RentJeeves\ComponentBundle\PidKiqProcessor\Exception\RuntimeException;
use RentJeeves\ComponentBundle\PidKiqProcessor\Experian\ExperianPidKiqApiClient;

class PidKiqProcessorExperian extends PidKiqProcessorBase
{
    /**
     * @var ExperianPidKiqApiClient
     */
    protected $pidKiqApiClient;

    public function setPidKiqApiClient(ExperianPidKiqApiClient $pidKiqApiClient)
    {
        $this->pidKiqApiClient = $pidKiqApiClient;
    }

    /**
     * {@inheritdoc}
     */
    protected function internalRetrieveQuestions()
    {
        try {
            // 2. Get last model or create new if we can
            if ($this->getPidkiqModel() && !$this->getIsSuccessfull()) {
                return [];
            } else {
                // 3. Return questions from DB or retrieve it from Service
                $questions = $this->getPidkiqModel()->getQuestions();

                if ($this->getPidkiqModel()->getStatus() === PidkiqStatus::INPROGRESS && !empty($questions)) {
                    return $questions;
                } elseif ($this->getPidkiqModel()->getStatus() === PidkiqStatus::SUCCESS) {
                    return [];
                } else {
                    $questions = [];

                    $this->getPidkiqModel()->setStatus(PidkiqStatus::INPROGRESS);

                    $request = $this->prepareRetrieveQuestionsRequest();

                    // 4. Let's double check some things before sending questions
                    $response = $this->pidKiqApiClient->getQuestions($request);
                    $preciseIDServer = $response->getProducts()->getPreciseIDServer();

                    //    - First, OFAC result
                    if (1 != $preciseIDServer->getGLBDetail()->getCheckpointSummary()->getOFACValidationResult()) {
                        $this->getPidkiqModel()->setStatus(PidkiqStatus::UNABLE);
                        $this->setIsSuccessfull(false);

                    //    - Then, see if there is a victim statement on file
                    // 9001 - Consumer reported as deceased
                    // 9012 - Consumer/Victim Statement on-file
                    // 9013 - Blocked or Frozen file
                    } elseif ($preciseIDServer->getSummary()->getPreciseIDScore() > 9000) {
                        $this->getPidkiqModel()->setStatus(PidkiqStatus::LOCKED);
                        $this->setIsSuccessfull(false);

                    // Check KIQ processing successful by no questions returned
                    } elseif (1 == $preciseIDServer->getKbaScore()->getGeneral()->getKbaResultCode()) {
                        $this->getPidkiqModel()->setStatus(PidkiqStatus::UNABLE);
                        $this->setIsSuccessfull(false);

                    // Check No questions returned due to excessive use
                    } elseif (2 == $preciseIDServer->getKbaScore()->getGeneral()->getKbaResultCode()) {
                        $this->getPidkiqModel()->setStatus(PidkiqStatus::BACKOFF);
                        $this->setIsSuccessfull(false);
                    } else {
                        $questions = $this->parseRetrieveQuestionsResponse($response);
                    }

                    $this->getPidkiqModel()->setSessionId($preciseIDServer->getSessionId());
                }
            }
        } catch (\Exception $e) {
            $this->getPidkiqModel()->setStatus(PidkiqStatus::BACKOFF);
            $this->setIsSuccessfull(false);
            $questions = [];
        }

        $this->getPidkiqModel()->setUser($this->getUser());
        $this->getPidkiqModel()->setTryNum($this->getPidkiqModel()->getTryNum() + 1);
        $this->getPidkiqModel()->setQuestions($questions);
        $this->getPidkiqModel()->setCheckSumm($this->getPidkiqCheckSum());
        $this->em->persist($this->getPidkiqModel());
        $this->em->flush();

        return $questions;
    }

    /**
     * @return NetConnectRequest
     */
    protected function prepareRetrieveQuestionsRequest()
    {
        $request = new NetConnectRequest();

        $preciseIDServer = $request->getRequest()->getProducts()->getPreciseIDServer();
        /** Init empty objects*/
        $preciseIDServer->getSubscriber();
        $preciseIDServer->getVendor();
        $preciseIDServer->getOptions();
        /** Mapping User to Applicant */
        $primaryApplicant = $preciseIDServer->getPrimaryApplicant();
        $primaryApplicant->getName()
            ->setFirst($this->getUser()->getFirstName())
            ->setSurname($this->getUser()->getLastName())
            ->setMiddle($this->getUser()->getMiddleInitial());

        $primaryApplicant->setSsn((int) $this->getUser()->getSsn());

        $primaryApplicant->getPhone()
            ->setNumber($this->getUser()->getPhone());

        if ($defaultAddress = $this->getUser()->getDefaultAddress()) {
            $primaryApplicant->getCurrentAddress()
                ->setCity($defaultAddress->getCity())
                ->setState($defaultAddress->getArea())
                ->setStreet('PO BOX 445')//$defaultAddress->getAddress())
                ->setZip($defaultAddress->getZip());
        }

//        $primaryApplicant->setDob($this->getUser()->getDBO());
        return $request;
    }

    /**
     *
     * @param NetConnectResponse $response
     * @return array
     */
    protected function parseRetrieveQuestionsResponse(NetConnectResponse $response)
    {
        $preciseIDServer = $response->getProducts()->getPreciseIDServer();

        $questions = [];

        if ($preciseIDServer->getError()->getErrorCode()) {
            throw new RuntimeException(
                $preciseIDServer->getError()->getErrorDescription(),
                $preciseIDServer->getError()->getErrorCode()
            );
        }

        if (null == $preciseIDServer->getKba()) {
            throw new RuntimeException(
                $preciseIDServer->getMessages()->getMessage()->getText(),
                E_USER_ERROR
            );
        }

        /** @var QuestionSet $question */
        foreach ($preciseIDServer->getKba()->getQuestionSet() as $question) {
            $questionArr = $question->getQuestionChoices();
            array_unshift($questionArr, null);
            unset($questionArr[0]);
            $questions[$question->getQuestionText()] = $questionArr;
        }

        return $questions;
    }

    /**
     * {@inheritdoc}
     */
    protected function internalProcessAnswers(array $answers)
    {
        if (PidkiqStatus::INPROGRESS !== $this->getPidkiqModel()->getStatus() || !$this->getIsSuccessfull()) {
            return false;
        }

        $request = $this->prepareProcessAnswersRequest($answers, $this->getPidkiqModel()->getSessionId());

        $response = $this->pidKiqApiClient->getResult($request);

        return $this->parseProcessAnswersResponse($response);
    }

    /**
     * @param array $answers
     * @param string $sessionId
     * @return NetConnectRequest
     */
    protected function prepareProcessAnswersRequest(array $answers, $sessionId)
    {
        $request = new NetConnectRequest();

        $outWalletAnswerData = $request
            ->getRequest()
            ->getProducts()
            ->getPreciseIDServer()
            ->getKbaAnswers()
            ->getOutWalletAnswerData();

        $outWalletAnswerData
            ->setSessionID($sessionId)
            ->getOutWalletAnswers()
                ->setOutWalletAnswer1(array_shift($answers))
                ->setOutWalletAnswer2(array_shift($answers))
                ->setOutWalletAnswer3(array_shift($answers))
                ->setOutWalletAnswer4(array_shift($answers));

        return $request;
    }

    /**
     * @param NetConnectResponse $response
     * @return bool
     */
    protected function parseProcessAnswersResponse(NetConnectResponse $response)
    {
        $preciseIDServer = $response->getProducts()->getPreciseIDServer();

        if ('ACC' !== $preciseIDServer->getKbaScore()->getScoreSummary()->getAcceptReferCode() ||
            'REF' !== $preciseIDServer->getKbaScore()->getScoreSummary()->getAcceptReferCode()) {
            return false;
        }

        if ('N' !== trim($preciseIDServer->getGLBDetail()->getCheckpointSummary()->getHighRiskAddrCode())) {
            return false;
        }

        if (0 == $preciseIDServer->getGLBDetail()->getCheckpointSummary()->getAddrResMatches()) {
            return false;
        }

        if (1 != $preciseIDServer->getGLBDetail()->getCheckpointSummary()->getDateOfBirthMatch()) {
            return false;
        }

        if (0 == $preciseIDServer->getGLBDetail()->getCheckpointSummary()->getPhnResMatches()) {
            return false;
        }

        if ($preciseIDServer->getSummary()->getPreciseIDScore() < 550) {
            return false;
        }

        return true;
    }
}
