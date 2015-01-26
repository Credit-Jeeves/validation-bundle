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
        $this->assertCount(1, $resManSettings);
        $this->assertCount(1, $accountingSettings);

        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_holdings'));
        $tableBlock->clickLink('link_add');
        $this->assertNotNull($textFields = $this->page->findAll('css', 'input[type=text]'));
        $this->assertCount(9, $textFields);
        $textFields[0]->setValue('Test');
        $this->assertNotNull(
            $links = $this->page->findAll(
                'css',
                '.nav-tabs a'
            )
        );
        $links[2]->click();
        $this->assertNotNull($textField = $this->page->find('css', 'input[type=url]'));
        $textField->setValue('https://www.iyardiasp.com/8223thirdparty708dev/');
        $textFields[1]->setValue('renttrackws');
        $textFields[2]->setValue('57742');
        $textFields[3]->setValue('sdb17\SQL2k8_R2');
        $textFields[4]->setValue('afqoml_70dev');
        $textFields[5]->setValue('SQL Server');
        $links[3]->click();
        $textFields[8]->setValue('728192738921738927398');
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
        $this->assertCount(9, $textFields);
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
        $this->assertCount(2, $resManSettings);
        $this->assertCount(2, $accountingSettings);
    }
}
