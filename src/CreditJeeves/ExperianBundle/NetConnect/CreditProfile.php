<?php
namespace CreditJeeves\ExperianBundle\NetConnect;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use CreditJeeves\ExperianBundle\NetConnect;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * It gets credit reports through NetConnect service
 *
 * DI\Service("experian.net_connect.credit_profile") It is defined in services.yml
 */
class CreditProfile extends NetConnect
{
    /**
     * Current url
     *
     * @var string
     */
    protected $isUrlInit = false;
    protected $d2cSubCode = false;

    /**
     * @inheritdoc
     */
    public function setConfigs($url, $dbHost, $subCode, $d2cSubCode = null)
    {
        $this->url = $url;
        $this->getNetConnectRequest()
            ->setEai($this->settings->getCreditProfileEai())
            ->setDbHost($dbHost);
        $this->getNetConnectRequest()
            ->getRequest()->getProducts()->getCreditProfile()->getSubscriber()->setSubCode($subCode);
        $this->d2cSubCode = $d2cSubCode;
        $this->usrPwd = $this->settings->getCreditProfileUserPwd();
        return $this;
    }

    /**
     * Initialize D2C credit profiles attributes
     *
     * @return $this
     */
    public function initD2c()
    {
        $this->getNetConnectRequest()
            ->getRequest()
            ->getProducts()
            ->getCreditProfile()
            ->getSubscriber()
            ->setSubCode($this->d2cSubCode);

        return $this;
    }

    /**
     * Retrieve current url from service
     */
    public function initNetConnectUrl()
    {
        if (!$this->isUrlInit) {
            $url = $this->doRequest('', '-InitUrl', 'GET');
            if (!$this->validateReceivedUrl($url)) {
                throw new Exception("Received url '{$url}' invalid", E_ERROR);
            }
            $this->url = $url;
            $this->isUrlInit = true;
            $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }
    }

    /**
     * Validates returned url of Experian
     *
     * @param string $url
     *
     * @return boolean
     */
    public function validateReceivedUrl($url)
    {
        return (bool)preg_match('/https:\/\/(.*\.)?experian\.com\/.+/i', $url);
    }

    /**
     * @param string $xml
     *
     * @return string
     */
    protected function composeRequest($xml)
    {
        $this->log($xml, '-XML-Request');
        return "&NETCONNECT_TRANSACTION=" . urlencode($xml);
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
        $creditProfile = $this->getNetConnectRequest()->getRequest()->getProducts()->getCreditProfile();
        $this->addUserToRequest(
            $user,
            $creditProfile->getPrimaryApplicant()
        );
        $creditProfile->getAccountType();
        $creditProfile->getAddOns();
        $creditProfile->getSubscriber();
        $creditProfile->getOutputType();
        $creditProfile->getVendor();
        $xml = $this->getSerializer()->serialize(
            $this->getNetConnectRequest(),
            'xml',
            $this->getSerializerContext('CreditProfile')
        );
        $this->validate($xml, 'NetConnect');
        return $xml;
    }

    /**
     * @param $response
     *
     * @throws Exception
     *
     * @return string
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
        if ($errorMessage = $netConnectResponse->getErrorMessage()) {
            throw new Exception($errorMessage);
        }
        $arfString = trim($netConnectResponse->getHostResponse());
        if (false !== strpos($arfString, '*****  NO RECORD FOUND  *****')) {
            throw new Exception('No record found');
        }
        return $arfString;
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
        $this->initNetConnectUrl();
        return $this->createResponse(
            $this->doRequest($this->composeRequest($this->createRequestOnUserData($user)))
        );
    }
}
