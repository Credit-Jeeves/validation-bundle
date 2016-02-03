<?php
namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class ReportingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function startReporting()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('mamazza@rentrack.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout+5000, "jQuery('#info-block').length > 0");
        $this->assertNotNull($button = $this->page->find('css', '#info-block button'));
        $button->click();
        $this->assertNotNull(
            $form = $this->page->find('css', '#reporting-form')
        );
        $this->fillForm(
            $form,
            [
                'rep-experian'  => true,
                'rep-tu'    => true,
                'rep-equifax' => true
            ]
        );

        $this->assertNotNull($button = $this->page->find('css', '#reporting-form a.button-link'));
        $button->click();

        $this->session->wait($this->timeout, "jQuery('#contracts-history').length > 0");
        $this->assertNotNull($link = $this->page->find('css', 'a span.reporting-action'));
        $link->click();
        $this->assertNotNull($checkboxes = $this->page->findAll('css', '.reporting-start input[type=checkbox]'));
        $this->assertEquals(3, count($checkboxes));
        $this->assertTrue($checkboxes[0]->isChecked());
        $this->assertTrue($checkboxes[1]->isChecked());
        $this->assertTrue($checkboxes[2]->isChecked());
    }
}
