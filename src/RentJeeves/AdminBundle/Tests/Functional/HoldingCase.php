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
        $links[1]->click();
        $this->assertNotNull($textField = $this->page->find('css', 'input[type=url]'));
        $textField->setValue('https://www.iyardiasp.com/8223thirdparty708dev/');
        $textFields[1]->setValue('renttrackws');
        $textFields[2]->setValue('57742');
        $textFields[3]->setValue('sdb17\SQL2k8_R2');
        $textFields[4]->setValue('afqoml_70dev');
        $textFields[5]->setValue('SQL Server');
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
            10000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            25000,
            "$('.overlay-trigger').length <= 0"
        );

        $this->assertNotNull($this->page->find('css', '.alert-success'));
        $this->assertNotNull(
            $links = $this->page->findAll(
                'css',
                '.nav-tabs a'
            )
        );
        $links[1]->click();
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
            10000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            35000,
            "$('.overlay-trigger').length <= 0"
        );
        $this->assertNotNull($this->page->find('css', '.alert-error'));
        $this->logout();
    }
}
