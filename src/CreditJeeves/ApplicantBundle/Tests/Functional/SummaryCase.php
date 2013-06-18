<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class SummaryCase extends BaseTestCase
{
    protected $fixtures = array(
        '001_cj_account_group.yml',
        '002_cj_admin_account.yml',
        '003_cj_dealer_account.yml',
        '004_cj_applicant.yml',
        '005_cj_lead.yml',
        '006_cj_applicant_report.yml',
        '007_cj_applicant_score.yml',
        '010_cj_affiliate.yml',
        '013_cj_holding_account.yml',
        '020_email.yml',
        '021_email_translations.yml',
    );

    /**
     * @test
     */
    public function userCreditBalance()
    {
        $this->load($this->fixtures, true);
        $this->setDefaultSession('goutte');
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
        $this->load($this->fixtures, true);
        $this->setDefaultSession('goutte');
        $this->login('emilio@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->assertNotNull($title = $this->page->find('css', '.summary h3'));
        $this->assertEquals('component.credit.summary', $title->getText(), 'Wrong title');
        $this->assertNotNull($blocks = $this->page->findAll('css', '.summary .summary-block'));
        $this->assertCount(3, $blocks, 'Wrong number of blocks');
        $this->logout();
    }
}
