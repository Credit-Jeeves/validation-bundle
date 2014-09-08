<?php
namespace CreditJeeves\ExperianBundle\NetConnect;

use CreditJeeves\ApiBundle\Util\ExceptionWrapper;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\ExperianBundle\Model\QuestionSet;
use CreditJeeves\ExperianBundle\NetConnect as Base;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;

/**
 * PreciseID (Pidkiq) is used for verifying user's identity.
 *
 * DI\Service("experian.net_connect.precise_id") It is defined in services.yml
 */
class PreciseID extends Base
{
    const SEPARATOR = '#';

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * XML
     * @var string
     */
    protected $lastResponse;

    /**
     * @inheritdoc
     */
    public function setConfigs($url, $dbHost, $subCode)
    {
        $this->url = $url;
        $this->getNetConnectRequest()
            ->setEai($this->settings->getPreciseIDEai())
            ->setDbHost($dbHost);
        $this->getNetConnectRequest()
            ->getRequest()->getProducts()->getPreciseIDServer()->getSubscriber()->setSubCode($subCode);

        $this->usrPwd = $this->settings->getPreciseIDUserPwd();
        return $this;
    }

    /**
     * @param $user
     *
     * @throws Exception
     *
     * @return string
     */
    protected function createRequestOnUserData($user)
    {
        $preciseIDServer = $this->getNetConnectRequest()->getRequest()->getProducts()->getPreciseIDServer();
        $this->addUserToRequest(
            $user,
            $preciseIDServer->getPrimaryApplicant()
        );
        $preciseIDServer->getSubscriber();
        $preciseIDServer->getVendor();
        $preciseIDServer->getOptions();
        $xml = $this->getSerializer()->serialize(
            $this->getNetConnectRequest(),
            'xml',
            $this->getSerializerContext('PreciseID')
        );
        $this->validate($xml, 'NCPreciseIDRequestV5_0');
        return $xml;
    }

    /**
     * @param NetConnectResponse $model
     *
     * @return array
     */
    public function retrieveUserData(NetConnectResponse $model)
    {
        $preciseIDServer = $model->getProducts()->getPreciseIDServer();
        $this->sessionId = $preciseIDServer->getSessionId();
        $questions = array();
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
     * @param User $user
     *
     * @throws Exception
     *
     * @return array
     */
    public function getResponseOnUserData(User $user)
    {
        $netConnectResponse = $this->createResponse($this->doRequest($this->createRequestOnUserData($user)));

        $preciseIDServer = $netConnectResponse->getProducts()->getPreciseIDServer();
        if (0 != $preciseIDServer->getKbaScore()->getGeneral()->getKbaResultCode()) {
            throw new Exception(
                $preciseIDServer->getKbaScore()->getGeneral()->getKbaResultCodeDescription(),
                E_USER_ERROR
            );
        }

        if (null == $preciseIDServer->getKba()) {
            throw new Exception(
                $preciseIDServer->getMessages()->getMessage()->getText(),
                E_USER_ERROR
            );
        }

        return $this->retrieveUserData($netConnectResponse);
    }

    /**
     * @param $user
     *
     * @throws Exception
     *
     * @return NetConnectResponse
     */
    public function getObjectOnUserData($user)
    {
        $netConnectResponse = $this->createResponse($this->doRequest($this->createRequestOnUserData($user)));
        $sharedApplication = $netConnectResponse->getProducts()->getPreciseIDServer()->getGLBDetail()
            ->getSharedApplication();
        $errors = $sharedApplication->getArrayOfErrors();
        if (!empty($errors) && isset($errors['3001'])) {
            // TODO use current SEPARATOR in ExceptionWrapper
            throw new Exception(implode(static::SEPARATOR, $errors), 400);
        }

        return $netConnectResponse;
    }

    /**
     * @param $response
     *
     * @throws Exception
     *
     * @return NetConnectResponse
     */
    protected function createResponse($response)
    {
        $this->lastResponse = $response;
        /**
         * @var NetConnectResponse $netConnectResponse
         */
        $netConnectResponse = $this->getSerializer()->deserialize(
            $response,
            'CreditJeeves\ExperianBundle\Model\NetConnectResponse',
            'xml'
        );

        $products = $netConnectResponse->getProducts();
        if (!$products) {
            throw new Exception("Don't have 'Products' in response");
        }
        $preciseIDServer = $products->getPreciseIDServer();
        if ($preciseIDServer->getError()->getErrorCode()) {
            throw new Exception(
                $preciseIDServer->getError()->getErrorDescription(),
                $preciseIDServer->getError()->getErrorCode()
            );
        }

        return $netConnectResponse;
    }

    /**
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Returns last session ID
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @param array $answers
     *
     * @return bool
     */
    public function getResult($sessionId, $answers)
    {
        $outWalletAnswerData = $this->getNetConnectRequest()
            ->getRequest()->getProducts()->getPreciseIDServer()->getKbaAnswers()->getOutWalletAnswerData();
        $outWalletAnswerData->setSessionID($sessionId);
        $outWalletAnswerData->getOutWalletAnswers()
            ->setOutWalletAnswer1(array_shift($answers))
            ->setOutWalletAnswer2(array_shift($answers))
            ->setOutWalletAnswer3(array_shift($answers))
            ->setOutWalletAnswer4(array_shift($answers));

        $xml = $this->getSerializer()->serialize(
            $this->getNetConnectRequest(),
            'xml',
            $this->getSerializerContext('PreciseIDQuestions')
        );
        $this->validate($xml, 'NCPreciseIDRequestV5_0');

        return 'ACC' == $this->createResponse($this->doRequest($xml, '-Questions'))
            ->getProducts()->getPreciseIDServer()->getKbaScore()->getScoreSummary()->getAcceptReferCode();
    }
}
