<?php

namespace CreditJeeves\CoreBundle\Partner;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("partner.creditcom")
 */
class CreditcomPartner
{
    const CREDITCOM_URL_TEMPLATE =
        'https://www.credit.com/subscriber-feedback/rent-track?ref-id=%s&price=%s&prod=%s&advtrans=%s';

    protected $browser;

    /**
     * @DI\InjectParams({
     *     "browser" = @DI\Inject("buzz.browser")
     * })
     */
    public function __construct($browser)
    {
        $this->browser = $browser;
    }

    public function charge($code, $price, $productName, $transactionId)
    {
        $url = sprintf(self::CREDITCOM_URL_TEMPLATE, $code, $price, $productName, $transactionId);

        return true;
//        $this->browser->get($url);
    }
} 
