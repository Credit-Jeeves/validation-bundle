<?php
namespace CreditJeeves\ExperianBundle\NetConnect;

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
     * @inheritdoc
     */
    public function setConfigs($url, $dbHost, $subCode)
    {
        $this->url = $url;
        $this->getNetConnectRequest()
            ->setEai($this->settings->getCreditProfileEai())
            ->setDbHost($dbHost);

        return $this;
    }
}
