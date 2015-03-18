<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class HoldingCase extends BaseTestCase
{

    /**
     * @test
     */
    public function create()
    {
        $this->load(true);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $resManSettings = $em->getRepository('RjDataBundle:ResManSettings')->findAll();
        $accountingSettings = $em->getRepository('RjDataBundle:AccountingSettings')->findAll();
        $mriSettings = $em->getRepository('RjDataBundle:MRISettings')->findAll();
        $this->assertCount(1, $resManSettings);
        $this->assertCount(1, $accountingSettings);
        $this->assertCount(1, $mriSettings);

        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_holdings'));
        $tableBlock->clickLink('link_add');
        $this->assertNotNull($textFields = $this->page->findAll('css', 'input[type=text]'));
        $this->assertCount(20, $textFields);
        $textFields[0]->setValue('Test');
        $this->assertNotNull(
            $links = $this->page->findAll(
                'css',
                '.nav-tabs a'
            )
        );
        $links[2]->click();
        $this->assertNotNull($urlTextFields = $this->page->findAll('css', 'input[type=url]'));
        $this->assertCount(2, $urlTextFields);
        $urlTextFields[0]->setValue('https://www.iyardiasp.com/8223thirdparty708dev/');
        $textFields[1]->setValue('renttrackws');
        $textFields[2]->setValue('57742');
        $textFields[3]->setValue('sdb17\SQL2k8_R2');
        $textFields[4]->setValue('afqoml_70dev');
        $textFields[5]->setValue('SQL Server');
        $links[3]->click();
        $textFields[8]->setValue('728192738921738927398');
        $links[4]->click();
        $textFields[9]->setValue('RENTTRACKAPI');
        $textFields[10]->setValue('k8raKFPJ');
        $textFields[11]->setValue('RENTTRACK');
        $textFields[12]->setValue('3D5C25981F2911DA566EA5AC363B1B9B5CA8A5AD75EEDECB1EC0EDA76902926A');
        $textFields[13]->setValue('FE11CEE9FB6FDB03AA3950E3769C342FD58E3089EBF5BAD52FBB7D32B6152421');

        $textFields[14]->setValue('@');
        $textFields[15]->setValue('C');
        $textFields[16]->setValue('CR');
        $textFields[17]->setValue('OP');
        $textFields[18]->setValue('RNT');
        $textFields[19]->setValue('C225999');

        $urlTextFields[1]->setValue('https://mri45pc.saas.mrisoftware.com/mriapiservices/api.asp');

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();
        $this->assertNotNull(
            $links = $this->page->findAll(
                'css',
                '.nav-tabs a'
            )
        );
        $links[1]->click();
        $this->assertNotNull($this->page->find('css', '.alert-success'));
        $this->assertNotNull(
            $test = $this->page->find(
                'css',
                '#test'
            )
        );

        $test->click();
        $this->session->wait(
            50000,
            "$('.alert-success').length > 0"
        );

        $this->assertNotNull($this->page->find('css', ''));
        $this->assertNotNull(
            $links = $this->page->findAll(
                'css',
                '.nav-tabs a'
            )
        );
        $links[2]->click();
        $this->assertCount(20, $textFields);
        $textFields[2]->setValue('57742111111111111');
        $this->assertNotNull(
            $test = $this->page->find(
                'css',
                '#test'
            )
        );
        $test->click();
        $this->session->wait(
            54000,
            "$('.alert-error').length > 0"
        );

        $this->assertNotNull($this->page->find('css', ''));
        $this->logout();

        $resManSettings = $em->getRepository('RjDataBundle:ResManSettings')->findAll();
        $accountingSettings = $em->getRepository('RjDataBundle:AccountingSettings')->findAll();
        $mriSettings = $em->getRepository('RjDataBundle:MRISettings')->findAll();

        $this->assertCount(2, $resManSettings);
        $this->assertCount(2, $accountingSettings);
        $this->assertCount(2, $mriSettings);
    }
}
