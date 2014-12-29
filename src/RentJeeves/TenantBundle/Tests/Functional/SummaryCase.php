<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use RentJeeves\DataBundle\Entity\Tenant;
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
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );
        $tenant->setIsVerified(UserIsVerified::NONE);
        $contracts = $tenant->getContracts();
        foreach ($contracts as $contract) {
            $contract->setTransUnionStartAt(null);
            $contract->setReportToTransUnion(false);
            $em->flush($contract);
        }
        $em->flush($tenant);
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

        $today = new \DateTime();
        self::$kernel = null;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );
        $contracts = $tenant->getContracts();
        /**
         * @var Contract $contract
         */
        foreach ($contracts as $contract) {
            $this->assertNotNull($contract->getTransUnionStartAt());
            $this->assertTrue($contract->getReportToTransUnion());
            $this->assertTrue(($contract->getTransUnionStartAt()->format('Ymd') === $today->format('Ymd')));
        }
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

    /**
     * @test
     */
    public function shouldCatchWsdlErrorNumber()
    {
        self::$kernel = null;
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );
        $tenant->setIsVerified(UserIsVerified::NONE);
        $tenant->setPhone('-');
        $contracts = $tenant->getContracts();
        foreach ($contracts as $contract) {
            $contract->setTransUnionStartAt(null);
            $contract->setReportToTransUnion(false);
            $em->flush($contract);
        }
        $em->flush($tenant);
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout+5000, "typeof $ !== undefined");
        $this->assertNotNull(
            $form = $this->page->find('css', '#rentjeeves_checkoutbundle_userdetailstype')
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "typeof $ !== undefined");
        $this->assertNotNull(
            $error = $this->page->find('css', '.attention-box')
        );
        $this->assertEquals(
            "Element 'Number': '-' is not a valid value of the local atomic type.",
            $error->getText()
        );
    }

    /**
     * @test
     */
    public function shouldCatchWsdlErrorName()
    {
        self::$kernel = null;
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com'
            )
        );
        $tenant->setIsVerified(UserIsVerified::NONE);
        $tenant->setFirstName("Mary Jane");
        $contracts = $tenant->getContracts();
        foreach ($contracts as $contract) {
            $contract->setTransUnionStartAt(null);
            $contract->setReportToTransUnion(false);
            $em->flush($contract);
        }
        $em->flush($tenant);
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout+5000, "typeof $ !== undefined");
        $this->assertNotNull(
            $form = $this->page->find('css', '#rentjeeves_checkoutbundle_userdetailstype')
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "typeof $ !== undefined");
        $this->assertNotNull(
            $error = $this->page->find('css', '.attention-box')
        );
        $this->assertEquals(
            "Element 'First': 'Mary Jane' is not a valid value of the local atomic type.",
            $error->getText()
        );
    }
}
