<?php

namespace RentJeeves\CoreBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class MobileDeniedCase extends BaseTestCase
{
    /**
     * @test
     */
    public function index()
    {
        //On task https://credit.atlassian.net/browse/RT-276
        //was changed, currently we need allow ipad/mobile
        $this->markTestSkipped(
            'This test does not needed anymore.'
        );
        $this->setDefaultSession('goutte');
        $this->session->setRequestHeader(
            'User-Agent',
            'Mozilla/4.0 (compatible; IEMobile; Windows NT 5.0; WOW64; Trident/4.0; SLCC1)'
        );
        $this->session->visit($this->getUrl() . 'login');
        $this->assertNotNull($messageBody = $this->page->find('css', '.message-body'));
        $this->assertTrue(($messageBody->getText() === 'access.denied.description'));
    }
}
