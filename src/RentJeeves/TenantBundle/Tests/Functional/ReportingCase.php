<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

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
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($button = $this->page->find('css', '#info-block div.infoBlock button.button'));
        $button->click();
        $this->assertNotNull($button = $this->page->find('css', '#blockPopupEditProperty button.button'));
        $button->click();
        $this->session->wait($this->timeout, "jQuery('#reporting-popup').css('display') == 'none'");
        $this->page->clickLink('rent.history');
        $this->assertNotNull($link = $this->page->find('css', 'a span.reporting-action'));
        $link->click();
        $this->session->wait($this->timeout, "jQuery('#contracts-history').length > 0");
        $this->assertNotNull($button = $this->page->find('css', '#stop-reporting'));
        $button->click();
        $this->session->wait($this->timeout, "jQuery('#reporting-stop').css('display') == 'none'");
        $this->logout();
    }
}
