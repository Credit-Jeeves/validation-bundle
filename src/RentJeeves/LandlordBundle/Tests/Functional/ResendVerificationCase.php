<?php

namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class ResendVerificationCase extends BaseTestCase
{
    /**
     * @test
     */
    public function resendVerification()
    {
        $this->setDefaultSession('goutte');
        $this->load(true);
        $this->loginByAccessToken('landlord1@example.com');

        $this->assertNotNull($alerts = $this->page->findAll('css', 'div.landlord-alert-text'));
        $this->assertEquals('landlord.alert.verify_email', $alerts[0]->getText());
    }
}
