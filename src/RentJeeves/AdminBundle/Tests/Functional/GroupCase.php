<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ImportBundle\PropertyImport\ImportPropertySettingsProvider;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class GroupCase extends BaseTestCase
{
    use AdminFormUniqueIdGetter;

    /**
     * @test
     */
    public function checkDepositAccountCreateAndUpdateInGroup()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Generic group');
        $this->assertNotNull($group, 'Check fixtures, group with name "Generic group" should exist');
        $this->assertCount(
            0,
            $group->getDepositAccounts(),
            'Check fixtures, group with name "Generic group" should not have deposit accounts'
        );
        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');

        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');
        $editLink = $this->getDomElement('a:contains("Generic group")', 'Edit link doesn\'t find for group');
        $editLink->click();
        $tabLink = $this->getDomElement('.nav-tabs li>a:contains("Deposit Accounts")');
        $tabLink->click();

        $uniqueId = $this->getUniqueId();

        $addAction = $this->getDomElement(
            '#field_actions_' . $uniqueId . '_depositAccounts>a',
            'Should be displayed add deposit account action button'
        );
        $addAction->click(); //add new Deposit Account
        $this->session->wait(
            $this->timeout,
            "$('.sonata-ba-tbody').children().length > 0"
        );
        $inputMerchant = $this->getDomElement('#' . $uniqueId . '_depositAccounts_0_merchantName');
        $inputMerchant->setValue('MerchantName');
        $addAction->click(); //add new Deposit Account again
        $this->session->wait(
            $this->timeout,
            '$("#field_widget_' . $uniqueId . '_depositAccounts tbody>tr").length == 2'
        );
        $inputMerchant = $this->getDomElement('#' . $uniqueId . '_depositAccounts_1_merchantName');
        $inputMerchant->setValue('MerchantName1');
        $submit = $this->getDomElement('.btn-primary', 'Can not find main submit btn');
        $submit->click();
        $this->getDomElements('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $tabLink->click();

        $select = $this->getDomElement('#' . $uniqueId . '_depositAccounts_1_paymentProcessor');
        $select->selectOption('aci');
        $submit->click();
        // changed uniqueId
        $uniqueId = $this->getUniqueId();
        $this->getEntityManager()->refresh($group);
        $this->assertCount(2, $group->getDepositAccounts(), 'Should be added 2 new deposit accounts');
        $tabLink->click();

        $removeActionCheckbox = $this->getDomElement('#' . $uniqueId . '_depositAccounts_0__delete');
        $removeActionCheckbox->check(); //remove one deposit account
        $submit->click();
        $this->getEntityManager()->refresh($group);
        $this->assertCount(1, $group->getDepositAccounts(), 'Should be removed 1 deposit account');
    }

    /**
     * Try to set pay balance only and change type of group to integrated
     * @test
     */
    public function settingFirst()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('700Credit');
        $this->assertNotNull($group, 'Check fixtures, group with name "700Credit" not found');
        $groupSettings = $group->getGroupSettings();
        $this->assertFalse(
            $groupSettings->getIsIntegrated(),
            sprintf('Check fixtures, group #%d should not be integrated', $group->getId())
        );
        $this->assertFalse(
            $groupSettings->getPayBalanceOnly(),
            sprintf('Check fixtures, group #%d should not have pay balance setting', $group->getId())
        );

        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');
        $editLink = $this->getDomElement('a:contains("700Credit")', 'Edit link doesn\'t find for group');
        $editLink->click();

        $tabLink = $this->getDomElement('.nav-tabs li>a:contains("Settings")');
        $tabLink->click();

        $uniqueId = $this->getUniqueId();

        $payBalanceOnlyCheckBox = $this->getDomElement(
            '#' . $uniqueId . '_groupSettings_payBalanceOnly',
            'Pay Balance Only settings not found'
        );
        $payBalanceOnlyCheckBox->check();
        $submit = $this->getDomElement('.btn-primary', 'Can not find main submit btn');
        $submit->click();
        $error = $this->getDomElement('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertEquals('pay.balance.only.error', $error->getText(), 'Incorrect error message');
        $tabLink->click();
        $isIntegratedCheckBox = $this->getDomElement(
            '#' . $uniqueId . '_groupSettings_isIntegrated',
            'Integrated settings not found'
        );
        $payBalanceOnlyCheckBox->check();
        $isIntegratedCheckBox->check();
        $submit->click();
        $this->assertNull($error = $this->page->find('css', '.sonata-ba-form-error li'), 'Should have no errors');

        $this->getEntityManager()->refresh($group);
        $groupSettings = $group->getGroupSettings();
        $this->assertTrue(
            $groupSettings->getIsIntegrated(),
            'Should be set is_integrated setting'
        );
        $this->assertTrue(
            $groupSettings->getPayBalanceOnly(),
            'Should be set pay_balance_only setting'
        );
    }

    /**
     * Try to set pay balance only to integrated group that have recurring payments
     *
     * @test
     */
    public function settingSecond()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotNull($group, 'Check fixtures, group with name "Test Rent Group" not found');
        $groupSettings = $group->getGroupSettings();
        $this->assertTrue(
            $groupSettings->getIsIntegrated(),
            sprintf('Check fixtures, group #%d should be integrated', $group->getId())
        );
        $this->assertFalse(
            $groupSettings->getPayBalanceOnly(),
            sprintf('Check fixtures, group #%d should not have pay balance setting', $group->getId())
        );

        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');

        $editLink = $this->getDomElement('a:contains("Test Rent Group")', 'Edit link doesn\'t find for group');
        $editLink->click();

        $tabLink = $this->getDomElement('.nav-tabs li>a:contains("Settings")');
        $tabLink->click();

        $uniqueId = $this->getUniqueId();

        $payBalanceOnlyCheckBox = $this->getDomElement(
            '#' . $uniqueId . '_groupSettings_payBalanceOnly',
            'Pay Balance Only settings not found'
        );
        $payBalanceOnlyCheckBox->check();

        $submit = $this->getDomElement('.btn-primary', 'Can not find main submit btn');
        $submit->click();
        $error = $this->getDomElement('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertEquals('pay.balance.only.reccuring_error', $error->getText(), 'Incorrect error message');
    }

    /**
     * @test
     */
    public function shouldCheckDebitCardSettings()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotEmpty($group, 'Check fixtures, should be present group with name "Test Rent Group"');
        $this->assertFalse(
            $group->getGroupSettings()->isAllowedDebitFee(),
            'Default value for allowed debit fee should be false'
        );
        $this->assertEmpty(
            $group->getGroupSettings()->getDebitFee(),
            'Default value for debit fee shold be empty'
        );
        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');

        $editLink = $this->getDomElement('a:contains("Test Rent Group")', 'Edit link doesn\'t find for group');
        $editLink->click();
        $tabLink = $this->getDomElement('.nav-tabs li>a:contains("Settings")');
        $tabLink->click();

        $uniqueId = $this->getUniqueId();
        $form = $this->getDomElement('form', 'Form should be present');

        $this->fillForm(
            $form,
            [
                $uniqueId . '_groupSettings_allowedDebitFee' => 1
            ]
        );
        $submit = $this->getDomElement('.btn-primary', 'Can not find main submit btn');
        $submit->click();
        $error = $this->getDomElement('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertEquals('admin.error.debit_payment_processor', $error->getText(), 'Incorrect error message');

        $tabLink->click();
        $this->fillForm(
            $form,
            [
                $uniqueId . '_groupSettings_paymentProcessor' => 'aci',
                $uniqueId . '_groupSettings_allowedDebitFee' => 1
            ]
        );
        $submit->click();
        $error = $this->getDomElement('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertEquals('admin.error.debit_fee_should_be_filled', $error->getText(), 'Incorrect error message');

        $tabLink->click();
        $this->fillForm(
            $form,
            [
                $uniqueId . '_groupSettings_paymentProcessor' => 'aci',
                $uniqueId . '_groupSettings_allowedDebitFee' => 1,
                $uniqueId . '_groupSettings_debitFee' => 20
            ]
        );
        $submit->click();
        $this->getDomElement('.alert-success', 'We should get success');

        $this->getEntityManager()->refresh($group);
        $this->assertTrue(
            $group->getGroupSettings()->isAllowedDebitFee(),
            'Allowed debit fee should be updated '
        );
        $this->assertEquals(20, $group->getGroupSettings()->getDebitFee(), 'Debit fee should be set to 20');
    }

    /**
     * @test
     */
    public function shouldCheckImportSettings()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Generic group');
        $this->assertNotEmpty($group, 'Check fixtures, should be present group with name "Generic group"');
        $this->assertFalse(
            $group->isExistImportSettings(),
            'Check fixtures, group with name "Generic group" should not have import settings'
        );
        $this->setDefaultSession('selenium2');

        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');

        $editLink = $this->getDomElement('a:contains("Generic group")', 'Edit link doesn\'t find for group');
        $editLink->click();
        $tabLink = $this->getDomElement('.nav-tabs li>a:contains("Import Defaults")');
        $tabLink->click();

        $uniqueId = $this->getUniqueId();

        $sourceApi = $this->getDomElement(
            'input[type="radio"][name="' . $uniqueId . '[importSettings][source]"][value="integrated_api"]',
            'Radio button for Source "Integrated Api" should be present'
        );
        $sourceApi->click();
        // check that we show just api settings
        $propertyIdsField = $this->getDomElement('#' . $uniqueId . '_importSettings_apiPropertyIds');
        $csvFieldDelimiterField = $this->getDomElement('#' . $uniqueId . '_importSettings_csvFieldDelimiter');
        $csvTextDelimiterField = $this->getDomElement('#' . $uniqueId . '_importSettings_csvTextDelimiter');
        $csvDateFormatField = $this->getDomElement('#' . $uniqueId . '_importSettings_csvDateFormat');

        $this->assertTrue($propertyIdsField->isVisible(), 'Field "Property Ids" should be displayed');

        $this->assertFalse($csvFieldDelimiterField->isVisible(), 'Field "Field Delimiter" should not be displayed');
        $this->assertFalse($csvTextDelimiterField->isVisible(), 'Field "Text Delimiter" should not be displayed');
        $this->assertFalse($csvDateFormatField->isVisible(), 'Field "Date Format" should not be displayed');

        $importTypes = $this->getDomElements('#' . $uniqueId . '_importSettings_importType option');
        $this->assertCount(1, $importTypes, 'Should be displayed just 1 import type for api source');
        $this->assertEquals(
            'multi_properties',
            $importTypes[0]->getValue(),
            'Should be displayed "Single Group with Multi Properties"'
        );

        $propertyIdsField->setValue('');

        $submit = $this->getDomElement('.btn-primary', 'Can not find main submit btn');
        $submit->click();

        $errors = $this->getDomElements('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertCount(1, $errors, 'Should be displayed just 1 message');
        $this->assertEquals('admin.errors.api_property_ids.empty', $errors[0]->getText(), 'Incorrect error message');

        $tabLink->click();

        $propertyIdsField->setValue('*');
        $submit->click();

        $this->getEntityManager()->refresh($group);

        $this->assertTrue($group->isExistImportSettings(), 'Group should have import settings after save');
        $this->assertEquals(
            '*',
            $group->getImportSettings()->getApiPropertyIds(),
            'Api property ids should be set to "*"'
        );

        $uniqueId = $this->getUniqueId(); // should refresh uniqueId after success saving
        $tabLink->click();

        $sourceApi = $this->getDomElement(
            'input[type="radio"][name="' . $uniqueId . '[importSettings][source]"][value="integrated_api"]',
            'Radio button for Source "Integrated Api" should be present'
        );

        $this->assertTrue($sourceApi->isChecked(), 'Api Source should be checked');

        $sourceCSV = $this->getDomElement(
            'input[type="radio"][name="' . $uniqueId . '[importSettings][source]"][value="csv"]',
            'Radio button for Source CSC should be present'
        );
        $sourceCSV->click();
        // check that we can show just csv settings
        $propertyIdsField = $this->getDomElement('#' . $uniqueId . '_importSettings_apiPropertyIds');
        $csvFieldDelimiterField = $this->getDomElement('#' . $uniqueId . '_importSettings_csvFieldDelimiter');
        $csvTextDelimiterField = $this->getDomElement('#' . $uniqueId . '_importSettings_csvTextDelimiter');
        $csvDateFormatField = $this->getDomElement('#' . $uniqueId . '_importSettings_csvDateFormat');

        $this->assertFalse($propertyIdsField->isVisible(), 'Field "Property Ids" should not be displayed');

        $this->assertTrue($csvFieldDelimiterField->isVisible(), 'Field "Field Delimiter" should be displayed');
        $this->assertTrue($csvTextDelimiterField->isVisible(), 'Field "Text Delimiter" should be displayed');
        $this->assertTrue($csvDateFormatField->isVisible(), 'Field "Date Format" should be displayed');

        $importTypes = $this->getDomElements('#' . $uniqueId . '_importSettings_importType option');
        $this->assertCount(3, $importTypes, 'Should be displayed all import types for csv source');

        $csvFieldDelimiterField->setValue('');
        $csvDateFormatField->setValue('');
        $csvTextDelimiterField->setValue('');

        $submit->click();

        $errors = $this->getDomElements('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertCount(1, $errors, 'Should be displayed just 1 message');
        $this->assertEquals('admin.errors.csv_settings.empty', $errors[0]->getText(), 'Incorrect error message');

        $tabLink->click();

        $csvFieldDelimiterField->setValue('');
        $csvDateFormatField->setValue('F d, Y');
        $csvTextDelimiterField->setValue('"');

        $submit->click();

        $errors = $this->getDomElements('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertCount(1, $errors, 'Should be displayed just 1 message');
        $this->assertEquals('admin.errors.csv_settings.empty', $errors[0]->getText(), 'Incorrect error message');

        $tabLink->click();

        $csvFieldDelimiterField->setValue(',');
        $csvDateFormatField->setValue('');
        $csvTextDelimiterField->setValue('"');

        $submit->click();

        $errors = $this->getDomElements('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertCount(1, $errors, 'Should be displayed just 1 message');
        $this->assertEquals('admin.errors.csv_settings.empty', $errors[0]->getText(), 'Incorrect error message');

        $tabLink->click();

        $csvFieldDelimiterField->setValue(',');
        $csvDateFormatField->setValue('F d, Y');
        $csvTextDelimiterField->setValue('');

        $submit->click();

        $errors = $this->getDomElements('.sonata-ba-form-error ul>li', 'Should be displayed error');
        $this->assertCount(1, $errors, 'Should be displayed just 1 message');
        $this->assertEquals('admin.errors.csv_settings.empty', $errors[0]->getText(), 'Incorrect error message');

        $tabLink->click();

        $csvFieldDelimiterField->setValue(',');
        $csvDateFormatField->setValue('F d, Y');
        $csvTextDelimiterField->setValue('"');

        $submit->click();

        $this->getEntityManager()->refresh($group);

        $this->assertEquals(
            ',',
            $group->getImportSettings()->getCsvFieldDelimiter(),
            'Field Delimiter should be set to ","'
        );

        $this->assertEquals(
            '"',
            $group->getImportSettings()->getCsvTextDelimiter(),
            'Text Delimiter should be set to \'"\''
        );

        $this->assertEquals(
            'F d, Y',
            $group->getImportSettings()->getCsvDateFormat(),
            'Date Format should be set to \'"\''
        );
    }

    /**
     * @test
     */
    public function shouldCreateImportPropertiesJobsForGroupWithExtProperties()
    {
        $this->load(true);
        $group = $this->getEntityManager()->find('DataBundle:Group', 24);
        $group->getImportSettings()->setApiPropertyIds(ImportPropertySettingsProvider::YARDI_ALL_EXTERNAL_PROPERTY_IDS);
        $this->getEntityManager()->flush();

        $jobsCount = count($this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll());
        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');

        $importProperties = $this->getDomElements(
            'a:contains("admin.import.property")',
            'Import Property links not found'
        );
        $importProperties[2]->click();
        $this->getDomElement('.alert-success', 'Should get successful message');
        $this->assertCount(
            $jobsCount + 6, //6 because 3 for import and 3 for check status
            $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll(),
            'Job not created'
        );
    }

    /**
     * @test
     */
    public function shouldShowErrorMessageIfPressImportPropertyForGroupWithoutExtPropertyId()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');

        $importProperties = $this->getDomElements(
            'a:contains("admin.import.property")',
            'Import Property links not found'
        );
        $importProperties[0]->click();
        $this->getDomElement('.alert-danger', 'Should get error for group which doesn\'t have externalProperties');
    }
}
