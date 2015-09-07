<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class HoldingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateHoldingWithYardiSettings()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_holdings'));
        $tableBlock->clickLink('link_add');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $action = $form->getAttribute('action');
        $uniqueId = substr($action, strpos($action, '=') + 1);

        $this->assertNotNull($holdingName = $this->page->find('css', "#{$uniqueId}_name"), 'Holding name not found');
        $holdingName->setValue('Test Holding');

        $this->assertNotNull(
            $tabLinks = $this->page->findAll('css', '.nav-tabs a'),
            'Tabs not found'
        );
        $tabLinks[2]->click(); // Yardi Settings

        $this->assertNotNull(
            $url = $this->page->find('css', "#{$uniqueId}_yardiSettings_url"),
            'YardiSettings url input not found'
        );
        $url->setValue('https://www.iyardiasp.com/8223thirdparty708dev/');
        $this->assertNotNull(
            $username = $this->page->find('css', "#{$uniqueId}_yardiSettings_username"),
            'YardiSettings username input not found'
        );
        $username->setValue('renttrackws');
        $this->assertNotNull(
            $passw = $this->page->find('css', "#{$uniqueId}_yardiSettings_password"),
            'YardiSettings password input not found'
        );
        $passw->setValue('57742');
        $this->assertNotNull(
            $databaseServer = $this->page->find('css', "#{$uniqueId}_yardiSettings_databaseServer"),
            'YardiSettings databaseServer input not found'
        );
        $databaseServer->setValue('sdb17\SQL2k8_R2');
        $this->assertNotNull(
            $databaseName = $this->page->find('css', "#{$uniqueId}_yardiSettings_databaseName"),
            'YardiSettings databaseName input not found'
        );
        $databaseName->setValue('afqoml_70dev');
        $this->assertNotNull(
            $platform = $this->page->find('css', "#{$uniqueId}_yardiSettings_platform"),
            'YardiSettings platform input not found'
        );
        $platform->setValue('SQL Server');

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'), 'Submit button not found');
        $submit->click();
        $this->assertNotNull($this->page->find('css', '.alert-success'), 'Successful message not found');
        $this->assertNotNull(
            $testYardiSettingsButton = $this->page->find('css', '#test'),
            'Test Yardi Settings button not found'
        );

        $testYardiSettingsButton->click();
        $this->session->wait(
            50000,
            "$('.alert-success').length > 0"
        );
    }

    /**
     * @test
     */
    public function shouldCreateHoldingWithResManSettings()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $em = $this->getEntityManager();
        $resManSettings = $em->getRepository('RjDataBundle:ResManSettings')->findAll();
        $this->assertCount(1, $resManSettings, 'DB should contain 1 ResMan settings record');

        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_holdings'));
        $tableBlock->clickLink('link_add');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $action = $form->getAttribute('action');
        $uniqueId = substr($action, strpos($action, '=') + 1);

        $this->assertNotNull($holdingName = $this->page->find('css', "#{$uniqueId}_name"), 'Holding name not found');
        $holdingName->setValue('Test Holding');

        $this->assertNotNull(
            $tabLinks = $this->page->findAll('css', '.nav-tabs a'),
            'Tabs not found'
        );
        $tabLinks[3]->click(); // ResMan Settings

        $this->assertNotNull(
            $accountId = $this->page->find('css', "#{$uniqueId}_resManSettings_accountId"),
            'ResManSettings accountId input not found'
        );
        $accountId->setValue('728192738921738927398');

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'), 'Submit button not found');
        $submit->click();
        $this->assertNotNull($this->page->find('css', '.alert-success'), 'Successful message not found');
        $resManSettings = $em->getRepository('RjDataBundle:ResManSettings')->findAll();
        $this->assertCount(2, $resManSettings, 'ResMan settings should have 2 records');
    }

    /**
     * @test
     */
    public function shouldCreateHoldingWithMRISettings()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $em = $this->getEntityManager();
        $mriSettings = $em->getRepository('RjDataBundle:MRISettings')->findAll();
        $this->assertCount(1, $mriSettings, 'DB should contain 1 MRI settings record');

        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_holdings'));
        $tableBlock->clickLink('link_add');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $action = $form->getAttribute('action');
        $uniqueId = substr($action, strpos($action, '=') + 1);

        $this->assertNotNull($holdingName = $this->page->find('css', "#{$uniqueId}_name"), 'Holding name not found');
        $holdingName->setValue('Test Holding');

        $this->assertNotNull(
            $tabLinks = $this->page->findAll('css', '.nav-tabs a'),
            'Tabs not found'
        );
        $tabLinks[4]->click(); // MRI Settings

        $this->assertNotNull(
            $url = $this->page->find('css', "#{$uniqueId}_mriSettings_url"),
            'MRISettings url input not found'
        );
        $url->setValue('https://mri45pc.saas.mrisoftware.com/mriapiservices/api.asp');
        $this->assertNotNull(
            $user = $this->page->find('css', "#{$uniqueId}_mriSettings_user"),
            'MRISettings user input not found'
        );
        $user->setValue('RENTTRACKAPI');
        $this->assertNotNull(
            $passw = $this->page->find('css', "#{$uniqueId}_mriSettings_password"),
            'MRISettings password input not found'
        );
        $passw->setValue('k8raKFPJ');
        $this->assertNotNull(
            $databaseName = $this->page->find('css', "#{$uniqueId}_mriSettings_databaseName"),
            'MRISettings databaseName input not found'
        );
        $databaseName->setValue('RENTTRACK');
        $this->assertNotNull(
            $partnerKey = $this->page->find('css', "#{$uniqueId}_mriSettings_partnerKey"),
            'MRISettings partnerKey input not found'
        );
        $partnerKey->setValue('3D5C25981F2911DA566EA5AC363B1B9B5CA8A5AD75EEDECB1EC0EDA76902926A');
        $this->assertNotNull(
            $hash = $this->page->find('css', "#{$uniqueId}_mriSettings_hash"),
            'MRISettings hash input not found'
        );
        $hash->setValue('FE11CEE9FB6FDB03AA3950E3769C342FD58E3089EBF5BAD52FBB7D32B6152421');
        $this->assertNotNull(
            $siteId = $this->page->find('css', "#{$uniqueId}_mriSettings_siteId"),
            'MRISettings siteId input not found'
        );
        $siteId->setValue('@');
        $this->assertNotNull(
            $chargeCode = $this->page->find('css', "#{$uniqueId}_mriSettings_chargeCode"),
            'MRISettings chargeCode input not found'
        );
        $chargeCode->setValue('RNT');
        $this->assertNotNull(
            $clientId = $this->page->find('css', "#{$uniqueId}_mriSettings_clientId"),
            'MRISettings clientId input not found'
        );
        $clientId->setValue('C225999');

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'), 'Submit button not found');
        $submit->click();
        $this->assertNotNull($this->page->find('css', '.alert-success'), 'Successful message not found');
        $mriSettings = $em->getRepository('RjDataBundle:MRISettings')->findAll();
        $this->assertCount(2, $mriSettings, 'DB should contain 2 MRI settings record');
    }

    /**
     * @test
     */
    public function shouldCreateHoldingWithAMSISettings()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $em = $this->getEntityManager();
        $amsiSettings = $em->getRepository('RjDataBundle:AMSISettings')->findAll();
        $this->assertCount(1, $amsiSettings, 'DB should contain 1 AMSI settings record');

        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_holdings'));
        $tableBlock->clickLink('link_add');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $action = $form->getAttribute('action');
        $uniqueId = substr($action, strpos($action, '=') + 1);

        $this->assertNotNull($holdingName = $this->page->find('css', "#{$uniqueId}_name"), 'Holding name not found');
        $holdingName->setValue('Test Holding');

        $this->assertNotNull(
            $tabLinks = $this->page->findAll('css', '.nav-tabs a'),
            'Tabs not found'
        );
        $tabLinks[5]->click(); // AMSI Settings

        $this->assertNotNull(
            $url = $this->page->find('css', "#{$uniqueId}_amsiSettings_url"),
            'AMSISettings url input not found'
        );
        $url->setValue('https://amsitest.infor.com/amsiweb/edexweb/esite/leasing.asmx');
        $this->assertNotNull(
            $user = $this->page->find('css', "#{$uniqueId}_amsiSettings_user"),
            'AMSISettings user input not found'
        );
        $user->setValue('RentTrack');
        $this->assertNotNull(
            $passw = $this->page->find('css', "#{$uniqueId}_amsiSettings_password"),
            'AMSISettings password input not found'
        );
        $passw->setValue('RentTrack');
        $this->assertNotNull(
            $portfolioName = $this->page->find('css', "#{$uniqueId}_amsiSettings_portfolioName"),
            'AMSISettings portfolioName input not found'
        );
        $portfolioName->setValue('RentTrack');

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'), 'Submit button not found');
        $submit->click();
        $this->assertNotNull($this->page->find('css', '.alert-success'), 'Successful message not found');
        $amsiSettings = $em->getRepository('RjDataBundle:AMSISettings')->findAll();
        $this->assertCount(2, $amsiSettings, 'DB should contain 2 AMSI settings record');
    }
}
