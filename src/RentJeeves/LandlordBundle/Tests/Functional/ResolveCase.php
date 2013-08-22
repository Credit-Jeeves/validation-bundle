<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class ResolveCase extends BaseTestCase
{
    /**
     * @test
     */
    public function resolveCancel()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "jQuery('div.actions-table-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', 'div.actions-table-block table tbody tr td div.action-resolve')
        );
        $this->assertCount(1, $resolve, 'Wrong number of resolve contracts');
        $resolve[0]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve').is(':visible')");
        $this->assertNotNull($buttons = $this->page->findAll('css', '#blockPopupEditProperty button.button'));
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[1]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve').is(':visible')");
        $this->logout();
    }
}
