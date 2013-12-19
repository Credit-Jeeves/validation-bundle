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
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(2, $resolve, 'Wrong number of resolve contracts');
        $resolve[0]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve').is(':visible')");
        $this->assertNotNull($buttons = $this->page->findAll('css', '#blockPopupEditProperty button.button'));
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[1]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve').is(':visible')");
        $this->logout();
    }

    /**
     * @test
     */
    public function resolveEmail()
    {
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(2, $resolve, 'Wrong number of resolve contracts');
        $resolve[0]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve').is(':visible')");
        $this->assertNotNull($buttons = $this->page->findAll('css', '#blockPopupEditProperty button.button'));
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $this->page->pressButton('conflict.resolve.action');
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve').is(':visible')");
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(2, $resolve, 'Wrong number of resolve contracts');
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
        $email = array_pop($email);
        $email->click();
        $this->page->clickLink('text/html');
        $this->logout();
    }

    /**
     * @test
     */
    public function resolvePaid()
    {
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(2, $resolve, 'Wrong number of resolve contracts');
        $resolve[0]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve').is(':visible')");
        $this->assertNotNull($checkboxes = $this->page->findAll('css', '#contract-resolve .checkbox'));
        $this->assertCount(2, $checkboxes, 'Wrong number of checkboxes');
        $checkboxes[1]->click();
        $this->assertNotNull($buttons = $this->page->findAll('css', '#blockPopupEditProperty button.button'));
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[0]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve').is(':visible')");
        $this->session->wait($this->timeout, "!jQuery('#actions-block .processPayment').is(':visible')");
        $this->assertNotNull($contracts = $this->page->findAll('css', '#actions-block table tbody tr'));
        $this->assertCount(1, $contracts);
        $this->logout();
    }

    /**
     * @test
     */
    public function resolveUnpaid()
    {
        $this->markTestIncomplete('Functional is not ready');
    }
}
