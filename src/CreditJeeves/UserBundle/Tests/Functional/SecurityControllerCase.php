<?php

namespace CreditJeeves\UserBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

class SecurityControllerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldHideLoginMessageIfDbHasEmptySettingsForLoginMessage()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');

        $this->assertNull($this->getLoginMessageFromDb(), 'Fixtures not should have Settings:loginMessage');

        $this->session->visit($this->getUrl() . 'login');

        $this->assertEmpty(
            $this->page->find('css', '#loginMessage'),
            'It is expected that the message will not be displayed but it is displayed'
        );
    }

    /**
     * @test
     */
    public function shouldShowLoginMessageIfDbHasNotEmptySettingsForLoginMessage()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');

        $settings = $this->getSettings();
        $settings->setLoginMessage($message = 'TestMessage');
        $this->getEntityManager()->flush($settings);

        $this->session->visit($this->getUrl() . 'login');

        $this->assertNotNull(
            $actualMessage = $this->page->find('css', '#loginMessage'),
            'The message is not displayed'
        );

        $this->assertEquals($message, $actualMessage->getHtml(), 'Messages are not identical');
    }

    /**
     * @return string|null
     */
    private function getLoginMessageFromDb()
    {
        return $this->getSettings()->getLoginMessage();
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\Settings
     */
    private function getSettings()
    {
        return $this->getEntityManager()->getRepository('DataBundle:Settings')->findAll()[0];
    }
}
