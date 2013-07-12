<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class SummaryCase extends BaseTestCase
{
    /**
     * @test
     */
    public function userCreditBalance()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->login('emilio@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->assertNotNull($this->page->find('css', '.credit-balances-left'));
        $this->assertNotNull($this->page->find('css', '.credit-balances-right'));
        $this->assertNotNull($items = $this->page->findAll('css', '.credit-balances-block ul li'));
        $this->assertCount(3, $items, 'Wrong of items');
        $this->logout();
    }

    /**
     * @test
     * @depends userCreditBalance
     */
    public function userCreditSummary()
    {
        $this->load(false);
        $this->setDefaultSession('symfony');
        $this->login('emilio@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->assertNotNull($title = $this->page->find('css', '.summary h3'));
        $this->assertEquals('component.credit.summary', $title->getText(), 'Wrong title');
        $this->assertNotNull($blocks = $this->page->findAll('css', '.summary .summary-block'));
        $this->assertCount(3, $blocks, 'Wrong number of blocks');
        $this->logout();
    }
}
