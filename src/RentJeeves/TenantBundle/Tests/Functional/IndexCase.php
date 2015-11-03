<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class IndexCase extends BaseTestCase
{
    /**
     * @test
     */
    public function existPaymentsHistory()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
        $this->session->wait($this->timeout, "($('#tenant-payments table>tbody>tr').length === 10)");
        $this->assertNotNull($paymentsTable = $this->page->find('css', '#tenant-payments'));
        $this->assertNotNull($payments = $this->page->findAll('css', '#tenant-payments table>tbody>tr'));
        $this->assertEquals(10, count($payments));
        $this->assertNotNull($pages = $this->page->find('css', '.pagination-box'));
        $this->assertEquals('1 2 3 4', $pages->getText());

        $this->assertNotNull($filterPayments_link = $this->page->find('css', '#selContract_link'));
        $filterPayments_link->click();
        $this->assertNotNull($contract1 = $this->page->find('css', '#selContract_li_1'));
        $this->assertNotNull($noDataTitle = $this->page->find('css', '.notHaveData'));
        $this->assertFalse($noDataTitle->isVisible());
        $contract1->click();
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertTrue($noDataTitle->isVisible());

        $filterPayments_link->click();
        $this->assertNotNull($contract2 = $this->page->find('css', '#selContract_li_2'));
        $contract2->click();
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertNotNull($payments = $this->page->findAll('css', '#tenant-payments table>tbody>tr'));
        $this->assertEquals(10, count($payments));
        $this->assertNotNull($pages = $this->page->find('css', '.pagination-box'));
        $this->assertEquals('1 2', $pages->getText());
        $this->assertNotNull($pageLinks = $this->page->findAll('css', '.pagination-box>ul>li>a'));
        $pageLinks[count($pageLinks) - 1]->click();
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertNotNull($payments = $this->page->findAll('css', '#tenant-payments table>tbody>tr'));
        $this->assertEquals(2, count($payments));
        $this->logout();
    }

    /**
     * @test
     */
    public function shouldVerifiedTenant()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout + 5000, "typeof $ !== undefined");
        $this->assertNotNull(
            $form = $this->page->find('css', '#rentjeeves_checkoutbundle_userdetailstype'),
            'User details form should be displayed'
        );

        $this->page->clickLink('common.add_new'); // add new address
        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_ssn1').val('666')"
        );
        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_ssn2').val('30')"
        );
        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_ssn3').val('9041')"
        );

        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_again_ssn1').val('666')"
        );
        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_again_ssn2').val('30')"
        );
        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_again_ssn3').val('9042')"
        );

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_userdetailstype_new_address_street' => 'Street',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_city' => 'City',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_area' => 'CA',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_zip' => '90210',
            ]
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->session->wait($this->timeout, "!$('.overlay-trigger').is(':visible')");
        $this->session->wait($this->timeout, '$("#action_plan_page form").length');

        $this->assertNotEmpty(
            $errors = $this->page->findAll(
                'css',
                '#rentjeeves_checkoutbundle_userdetailstype_ssn_row ul.error_list li'
            ),
            'Should displayed error that ssn does\'t match.'
        );
        $this->assertCount(1, $errors, 'Should be displayed just one error');
        $this->assertEquals(
            'error.user.ssn.match',
            $errors[0]->getText(),
            sprintf('Should be displayed error: "error.user.ssn.match", expected: "%s"', $errors[0]->getText())
        );

        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_again_ssn1').val('666')"
        );
        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_again_ssn2').val('30')"
        );
        $this->session->evaluateScript(
            "$('#ssn_rentjeeves_checkoutbundle_userdetailstype_ssn_ssn_again_ssn3').val('9041')"
        );
        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_userdetailstype_new_address_street' => 'Street',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_city' => 'City',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_area' => 'CA',
                'rentjeeves_checkoutbundle_userdetailstype_new_address_zip' => '90210',
            ]
        );
        $this->page->pressButton('pay_popup.step.next');

        $this->assertNotNull(
            $form = $this->page->find('css', '#questions'),
            'Form with questions should be displayed'
        );
        //Fill correct answer
        $this->fillForm(
            $form,
            [
                'questions_OutWalletAnswer1_0' => true,
                'questions_OutWalletAnswer2_1' => true,
                'questions_OutWalletAnswer3_2' => true,
                'questions_OutWalletAnswer4_3' => true,
            ]
        );
        $this->page->pressButton('pay_popup.step.3');
        $this->assertNotNull($loading = $this->page->find('css', '.loading'));
        $this->session->wait($this->timeout + 5000, "window.location.pathname.match('\/summary') === null");
    }

    /**
     * @test
     */
    public function shouldNotAcceptPayment()
    {
        $this->load(true);
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
         * @var $contract Contract
         */
        foreach ($contracts as $contract) {
            $contract->setPaymentAccepted(PaymentAccepted::DO_NOT_ACCEPT);
            $groupSetting = $contract->getGroup()->getGroupSettings();
            $groupSetting->setIsIntegrated(true);
            $em->persist($groupSetting);
            $em->persist($contract);
        }
        $em->flush();
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($denied = $this->page->findAll('css', '.denied'));
        $this->assertEquals(5, count($denied));
        for ($i = 0; $i <= 3; $i++) {
            $this->assertEquals(
                'yardi.tenant.property.manager_disabled_payment',
                $denied[0]->getAttribute('title')
            );
        }
    }

    /**
     * @test
     * @depends shouldNotAcceptPayment
     */
    public function shouldAcceptPayment()
    {
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
         * @var $contract Contract
         */
        foreach ($contracts as $contract) {
            $contract->setPaymentAccepted(PaymentAccepted::ANY);
            $groupSetting = $contract->getGroup()->getGroupSettings();
            $groupSetting->setIsIntegrated(true);
            $em->persist($groupSetting);
            $em->persist($contract);
        }
        $em->flush();
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($denied = $this->page->findAll('css', '.denied'));
        $this->assertEquals(0, count($denied));
    }
}
