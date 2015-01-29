<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class DashboardCase extends BaseTestCase
{
    /**
     * @test
     */
    public function userDashboardScore()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->login('emilio@example.com', 'pass');
        $this->assertNotNull($score = $this->page->find('css', '.score-current'));
        $this->assertEquals(530, $score->getText(), 'Wrong score');
        $this->assertNotNull($score = $this->page->find('css', '.score-target'));
        $this->assertEquals(620, $score->getText(), 'Wrong score');
        $this->logout();
    }

    /**
     * @test
     */
    public function userDashboardElements()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', 'pass');
        $this->assertNotNull($target = $this->page->find('css', '.target-name span'));
        $this->assertEquals('Honda Civic', $target->getText(), 'Wrong target');

        // userFeaturedOffer
        $this->assertNotNull($offer = $this->page->find('css', '.barclaycard-intro h1'));
        $this->assertEquals('Featured Offer', $offer->getText(), 'Wrong offer');

        // userAccountStatus
        $this->assertNotNull($this->page->find('css', '#account-status'));
        $this->assertNotNull($header = $this->page->find('css', '#account-status h3'));
        $this->assertEquals('component.account_status.header', $header->getText(), 'Wrong header');
        $this->assertNotNull($accounts = $this->page->findAll('css', '#account-status .zebra-grid ul li'));
        $this->assertCount(9, $accounts, 'Wrong number of accounts');

        // userDidYouKnow
        $this->assertNotNull($this->page->find('css', '#did-you-know'));
        $this->assertNotNull($title = $this->page->find('css', '#did-you-know h3'));
        $this->assertEquals('component.didyouknow.title', $title->getText(), 'Wrong title');
//         $this->assertNotNull($header = $this->page->find('css', '#did-you-know strong'));
//         $this->assertEquals('dyk.subheading.default', $header->getText(), 'Wrong header');
//         $this->assertNotNull($text = $this->page->find('css', '#did-you-know .spaced-text'));
//         $this->assertEquals('component.didyouknow.text', $text->getText(), 'Wrong text');

        // userChangeLead
        $this->assertNotNull($select = $this->page->find('css', '#lead-select-button'));
        $select->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#lead-select-form .lead-select-lead').length > 0"
        );
        $this->assertNotNull($form = $this->page->find('css', '#lead-select-form'));
        $this->assertNotNull($links = $this->page->findAll('css', '.lead-select-lead'));
        $this->assertCount(3, $links, 'Wrong number of accounts');
        $links[0]->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#lightbox-container').css('display') == 'none'"
        );
        $this->session->wait(
            $this->timeout,
            "jQuery('#simulation-container .overlay').length > 0"
        );
        $this->session->wait(
            $this->timeout,
            "jQuery('#simulation-container .overlay').length == 0"
        );
        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('.target-name span').length > 0"
        );
        $this->assertNotNull($target = $this->page->find('css', '.target-name span'));
        $this->assertEquals('BMW X5', $target->getText(), 'Wrong target');
        $select->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#lead-select-form .lead-select-lead').length > 0"
        );
        $links[1]->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#lightbox-container').css('display') == 'none'"
        );

        $this->session->wait(
            $this->timeout + $this->timeout,
            "jQuery('.target-name span').text() == 'Honda Civic'"
        );
        $this->assertNotNull($target = $this->page->find('css', '.target-name span'));
        $this->assertEquals('Honda Civic', $target->getText(), 'Wrong target');
        $this->assertNotNull($success = $this->page->find('css', '.success-title'));
        $this->assertNotNull($score = $this->page->find('css', '.score-target'));
        $this->assertEquals(510, $score->getText(), 'Wrong score');
        $this->logout();
    }

    /**
     * @test
     * @depends userDashboardElements
     */
    public function userIncentives()
    {
        $this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', 'pass');
        $this->assertNull($this->page->find('css', '#addressed-items'));
        $this->assertNotNull($this->page->find('css', '#action-steps'));
        $this->assertNotNull($fixed = $this->page->findAll('css', '#action-steps ul li a.fixed'));
        $this->assertCount(6, $fixed, 'Wrong links');
        $fixed[0]->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#action-steps ul li a.fixed').length < 6"
        );
        $this->assertNotNull($fixed = $this->page->findAll('css', '#action-steps ul li a.fixed'));
        $this->assertCount(5, $fixed, 'Wrong fixed links');
        $this->assertNotNull($undo = $this->page->findAll('css', '#action-steps ul li a.rollback'));
        $this->assertCount(1, $undo, 'Wrong undo links');
        $undo[0]->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#action-steps ul li a.fixed').length > 5"
        );
        $this->assertNotNull($fixed = $this->page->findAll('css', '#action-steps ul li a.fixed'));
        $this->assertCount(6, $fixed, 'Wrong fixed links');
        $fixed[0]->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#action-steps ul li a.fixed').length < 6"
        );
        $this->assertNotNull($complete = $this->page->findAll('css', '#action-steps ul li a.completed'));
        $this->assertCount(1, $complete, 'Wrong undo links');
        $complete[0]->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#action-steps ul li a.completed').length < 1"
        );
        $this->assertNotNull($this->page->find('css', '#addressed-items'));
        $this->assertNotNull($incentives = $this->page->findAll('css', '#tradeline-incentives li'));
        $this->assertCount(1, $incentives, 'Wrong undo links');
        $this->logout();
        $this->setDefaultSession('goutte');
    }

    /**
     * Good, and our customer would visit the first page
     *
     * @depends userDashboardElements
     * @test
     */
    public function getReportPrequalAndAutoSimulation()
    {
        $this->markTestSkipped('This is an outdated test. CreditJevees no longer maintained. Can be removed.');
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('marion@example.com', 'pass');
        $this->session->wait($this->timeout + 10000, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout + 10000, "jQuery('#action_plan_page .score-column').children().length > 0");
        $this->assertNotNull($score = $this->page->find('css', '#action_plan_page .score-column .score-current'));
        $this->assertEquals(535, $score->getText(), 'Wrong score');
        $this->assertNotNull($score = $this->page->find('css', '#action_plan_page .score-column-target .score-target'));
        $this->assertEquals(600, $score->getText(), 'Wrong target score');
        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#simulation-container .action-steps ul').children().length > 0"
        );

        $this->assertNotNull($steps = $this->page->findAll('css', '#simulation-container .action-steps ul li'));
        $this->assertCount(1, $steps);
        $this->assertNotNull($stepsTitle = $this->page->find('css', '#simulation-container #steps-title'));
        $this->assertEquals('score-reach-title-message-0', $stepsTitle->getText());

        // manualSimulation
        $this->assertNotNull($form = $this->page->find('css', '#simulator_form'));
        $this->fillForm($form, array('money' => '700'));
        $this->assertNotNull($submit = $form->findButton('re-score'));
        $submit->click();
        $this->session->wait($this->timeout, "jQuery('#simulation-container .overlay').length > 0");
        $this->session->wait($this->timeout, "jQuery('#simulation-container .overlay').length == 0");
        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#simulation-container .action-steps ul').children().length > 0"
        );

        $this->assertNotNull($steps = $this->page->findAll('css', '#simulation-container .action-steps ul li'));
        $this->assertCount(4, $steps);
        $this->logout();
    }
}
