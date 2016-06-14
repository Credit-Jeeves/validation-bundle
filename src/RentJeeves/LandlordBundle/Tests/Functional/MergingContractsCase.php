<?php

namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class MergingContractsCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldFoundAndMergedContractsByResidentId()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->session->visit($this->getUrl() . 'landlord/tenants');

        $groupSelector = $this->getDomElement('.group-select>a', 'Can not find group selector');
        $groupSelector->click();

        $dtrGroupOption = $this->getDomElement(
            '#holding-group_list span:contains("First DTR Group")',
            'DTR Group not found on group selector'
        );
        $dtrGroupOption->click();
        $this->session->wait(2500); // refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table>tbody>tr').length > 0");

        $this->assertCount(
            3,
            $this->getDomElements('#contracts-block .properties-table>tbody>tr'),
            'Should be exist 3 contracts'
        );

        $this->getDomElement('.approve', 'Can not find Approve button')->click();

        $this->session->wait($this->timeout, "$('#tenant-approve-property-popup .footer-button-box').is(':visible')");

        $this
            ->getDomElement('#residentId', 'Resident ID field not found on approving dialog.')
            ->setValue('test_resident_1');

        $this->getDomElement('#approveTenant', 'Approve Contract btn not found')->click();

        $this->session->wait($this->timeout, "$('#contract-duplicate-popup').is(':visible')");

        $yesMergeBtn = $this
            ->getDomElement(
                '#contract-duplicate-popup .footer-button-box button:contains("contract.merging.yes")',
                'Yes button for merging contract should exist'
            );
        $this->assertTrue($yesMergeBtn->isVisible(), 'Yes button for merging contract should be visible');
        $yesMergeBtn->click();

        $this->session->wait($this->timeout, "$('#tenant-merge-contract-popup').is(':visible')");

        $saveMergeBtn = $this->getDomElement(
            '#tenant-merge-contract-popup .footer-button-box button:contains("savechanges")',
            'Save button for merging contract should exist'
        );
        $this->assertTrue($saveMergeBtn->isVisible(), 'Save button for merging contract should be visible');
        $saveMergeBtn->click();

        $this->session->wait(2500); // refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table>tbody>tr').length > 0");

        $this->assertCount(
            2,
            $this->getDomElements('#contracts-block .properties-table>tbody>tr'),
            'Should be retrieved just 2 contracts'
        );
    }

    /**
     * @test
     */
    public function shouldFoundAndMergedContractsByEmail()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->session->visit($this->getUrl() . 'landlord/tenants');

        $groupSelector = $this->getDomElement('.group-select>a', 'Can not find group selector');
        $groupSelector->click();

        $dtrGroupOption = $this->getDomElement(
            '#holding-group_list span:contains("First DTR Group")',
            'DTR Group not found on group selector'
        );
        $dtrGroupOption->click();
        $this->session->wait(2500); // refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table>tbody>tr').length > 0");

        $this->assertCount(
            3,
            $this->getDomElements('#contracts-block .properties-table>tbody>tr'),
            'Should be exist 3 contracts'
        );

        $this->getDomElement('#edit-24', 'Can not find edit waiting contract button')->click();

        $this->session->wait($this->timeout, "$('#tenant-edit-property-popup .footer-button-box').is(':visible')");

        $this
            ->getDomElement('#email-edit', 'Email edit field not found on edit waiting contract dialog.')
            ->setValue('treewolta.j@example.com');

        $this
            ->getDomElement(
                '#tenant-edit-property-popup .footer-button-box button:contains("savechanges")',
                'Save Contract btn not found on edit contract dialog'
            )->click();

        $this->session->wait($this->timeout, "$('#contract-duplicate-popup').is(':visible')");

        $yesMergeBtn = $this
            ->getDomElement(
                '#contract-duplicate-popup .footer-button-box button:contains("contract.merging.yes")',
                'Yes button for merging contract should exist'
            );
        $this->assertTrue($yesMergeBtn->isVisible(), 'Yes button for merging contract should be visible');
        $yesMergeBtn->click();

        $this->session->wait($this->timeout, "$('#tenant-merge-contract-popup').is(':visible')");

        $saveMergeBtn = $this->getDomElement(
            '#tenant-merge-contract-popup .footer-button-box button:contains("savechanges")',
            'Save button for merging contract should exist'
        );
        $this->assertTrue($saveMergeBtn->isVisible(), 'Save button for merging contract should be visible');
        $saveMergeBtn->click();

        $this->session->wait(2500); // refresh page
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#contracts-block .properties-table>tbody>tr').length > 0");

        $this->assertCount(
            2,
            $this->getDomElements('#contracts-block .properties-table>tbody>tr'),
            'Should be retrieved just 2 contracts'
        );
    }
}
