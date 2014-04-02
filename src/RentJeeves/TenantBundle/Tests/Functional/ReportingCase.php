<?php
namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class ReportingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function startReporting()
    {
        $this->markTestIncomplete('Finish');
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($button = $this->page->find('css', '#info-block div.infoBlock button.button'));
        $button->click();
        $this->assertNotNull($button = $this->page->find('css', '#blockPopupEditProperty a.button'));
        $button->click();
        $this->session->wait($this->timeout, "jQuery('#contracts-history').length > 0");
        $this->assertNotNull($link = $this->page->find('css', 'a span.reporting-action'));
        $link->click();
        $this->assertNotNull($button = $this->page->find('css', '#stop-reporting'));
        $button->click();
        $this->session->wait($this->timeout, "jQuery('#reporting-stop').css('display') == 'none'");
        $this->logout();
    }
}
