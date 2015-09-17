<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @TODO need refactoring - (if we will add field - all tests will fail), we need more specific selectors
 */
class GroupCase extends BaseTestCase
{
    /**
     * @test
     */
    public function checkDepositAccountCreateAndUpdateInGroup()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Generic group');
        $this->assertNotEmpty($group);
        $this->assertCount(0, $group->getBillingAccounts());
        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_groups'));

        $tableBlock->clickLink('link_list');

        $this->assertNotNull($edit = $this->page->findAll('css', 'a.edit_link'));
        $edit[0]->click();
        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[1]->click();
        $this->assertNotNull($buttonsAction = $this->page->findAll('css', '.sonata-ba-action'));
        $this->count(4, $buttonsAction);
        $buttonsAction[2]->click(); //add new Deposit Account
        $this->session->wait(
            10000,
            "$('.sonata-ba-tbody').children().length > 0"
        );
        $this->assertNotNull($inputText = $this->page->findAll('css', 'input[type=text]'));
        $this->assertCount(14, $inputText);
        $inputText[8]->setValue('MerchantName');
        $buttonsAction[2]->click(); //add new Deposit Account
        $this->session->wait(
            10000,
            "$('input[type=text]').length > 14"
        );
        $this->assertNotNull($inputText = $this->page->findAll('css', 'input[type=text]'));
        $this->assertCount(16, $inputText);
        $inputText[10]->setValue('MerchantName1');
        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();
        $this->assertNotNull($this->page->find('css', '.sonata-ba-form-error'));
        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[1]->click();
        $this->assertNotNull($select = $this->page->findAll('css', 'select'));
        $this->assertCount(13, $select);
        $select[3]->selectOption('aci');
        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();
        $this->getEntityManager()->refresh($group);
        $this->assertCount(2, $group->getDepositAccounts());
        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[1]->click();
        $this->assertNotNull($checkbox = $this->page->findAll('css', 'input[type=checkbox]'));
        $this->assertCount(10, $checkbox);
        $checkbox[0]->check(); //remove one deposit account
        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();
        $this->getEntityManager()->refresh($group);
        $this->assertCount(1, $group->getDepositAccounts());
    }

    /**
     * @test
     */
    public function settingFirst()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneBy(['name' => '700Credit']);

        $this->assertNotEmpty($group, 'Check fixtures group with name 700Credit not found');
        $groupSettings = $group->getGroupSettings();

        $this->assertFalse(
            $groupSettings->getIsIntegrated(),
            sprintf('Check fixtures group #%d should not be integrated', $group->getId())
        );
        $this->assertFalse(
            $groupSettings->getPayBalanceOnly(),
            sprintf('Check fixtures group #%d should not have pay balance setting', $group->getId())
        );

        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_groups'));

        $tableBlock->clickLink('link_list');

        $this->assertNotNull($editLink = $this->page->find('css', 'a:contains("700Credit")'));
        $editLink->click();

        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[4]->click(); // go to settings tag

        $this->assertNotNull(
            $payBalanceOnlyCheckBox = $this->page->find(
                'css',
                'input[type=checkbox][id*="_groupSettings_payBalanceOnly"]'
            ),
            'Pay Balance Only settings not found'
        );
        $payBalanceOnlyCheckBox->check();
        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();

        $this->assertNotNull($error = $this->page->find('css', '.sonata-ba-form-error li'));
        $this->assertEquals('pay.balance.only.error', $error->getText());

        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[4]->click();

        $this->assertNotNull(
            $isIntegratedCheckBox = $this->page->find(
                'css',
                'input[type=checkbox][id*="_groupSettings_isIntegrated"]'
            ),
            'Integrated settings not found'
        );

        $payBalanceOnlyCheckBox->check();
        $isIntegratedCheckBox->check();

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();

        $this->assertNull($error = $this->page->find('css', '.sonata-ba-form-error li'));

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
     * @test
     */
    public function settingSecond()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneBy(['name' => 'Test Rent Group']);

        $this->assertNotEmpty($group, 'Check fixtures group with name Test Rent Group not found');
        $groupSettings = $group->getGroupSettings();

        $this->assertTrue(
            $groupSettings->getIsIntegrated(),
            sprintf('Check fixtures group #%d should be integrated', $group->getId())
        );
        $this->assertFalse(
            $groupSettings->getPayBalanceOnly(),
            sprintf('Check fixtures group #%d should not have pay balance setting', $group->getId())
        );

        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_groups'));

        $tableBlock->clickLink('link_list');

        $this->assertNotNull($editLink = $this->page->find('css', 'a:contains("Test Rent Group")'));
        $editLink->click();

        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[4]->click(); // go to settings tag

        $this->assertNotNull(
            $payBalanceOnlyCheckBox = $this->page->find(
                'css',
                'input[type=checkbox][id*="_groupSettings_payBalanceOnly"]'
            ),
            'Pay Balance Only settings not found'
        );

        $payBalanceOnlyCheckBox->click();

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();

        $this->assertNotNull($error = $this->page->find('css', '.sonata-ba-form-error li'));
        $this->assertEquals('pay.balance.only.reccuring_error', $error->getText());
    }
}
