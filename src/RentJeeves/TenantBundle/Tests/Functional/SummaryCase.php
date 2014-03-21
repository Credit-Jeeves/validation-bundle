<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class SummaryCase extends BaseTestCase
{
    /**
     * @test
     */
    public function existAddress()
    {
        self::$kernel = null;
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout+5000, "typeof $ !== undefined");
        $this->assertNotNull(
            $form = $this->page->find('css', '#rentjeeves_checkoutbundle_userdetailstype')
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "typeof $ !== undefined");
        $this->page->pressButton('pay_popup.step.3');
        $this->assertNotNull(
            $error = $this->page->find('css', '.attention-box')
        );
        $this->assertEquals('pidkiq.error.incorrect.answer2', $error->getText());
        $this->assertNotNull($form = $this->page->find('css', '#questions'));
        //Fill correct answer
        $this->fillForm(
            $form,
            array(
                'questions_OutWalletAnswer1_0' => true,
                'questions_OutWalletAnswer2_1' => true,
                'questions_OutWalletAnswer3_2' => true,
                'questions_OutWalletAnswer4_3' => true,
            )
        );
        $this->page->pressButton('pay_popup.step.3');
        $this->assertNotNull($loading = $this->page->find('css', '.loading'));
        $this->session->wait($this->timeout+5000, "window.location.pathname.match('\/summary') === null");
        $this->assertNotNull($summaryPage = $this->page->find('css', '#summary_page'));
    }

    /**
     * @test
     */
    public function newAddress()
    {
        self::$kernel = null;
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout+5000, "typeof $ !== undefined");
        $this->assertNotNull(
            $form = $this->page->find('css', '#rentjeeves_checkoutbundle_userdetailstype')
        );
        $this->assertNotNull(
            $radio = $this->page->findAll('css', '.radio')
        );
        $this->assertEquals(2, count($radio));
        $this->assertNotNull(
            $addNew = $this->page->find('css', '.fields-box>a')
        );
        $addNew->click();
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_userdetailstype_new_address_street'  => 'Street',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_city'    => 'City',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_area'    => 'CA',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_zip'     => '90210',
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->page->clickLink('pay_popup.step.previous');
        $this->session->wait($this->timeout+5000, "typeof $ !== undefined");
        $this->assertNotNull(
            $radio = $this->page->findAll('css', '.radio')
        );
        $this->assertEquals(3, count($radio));
    }
}
