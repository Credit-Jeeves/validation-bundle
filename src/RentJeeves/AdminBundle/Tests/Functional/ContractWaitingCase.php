<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use Doctrine\ORM\EntityManager;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ContractWaitingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function checkMovingContract()
    {
        $this->load(true);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractWaiting, 'Should be only one item on the list');

        $this->setDefaultSession('symfony');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_contract_waiting'));

        $tableBlock->clickLink('link_list');
        $this->assertNotNull($edit = $this->page->find('css', '.edit_link'), 'Cannot find edit link');
        $edit->click();

        $this->assertNotNull($input = $this->page->findAll('css', 'input'));
        $this->assertEquals(12, count($input), 'Unexpected amount of input fields');
        $input[3]->setValue("200");
        $input[4]->setValue("615");
        $input[5]->setValue("test@mail.com");
        $input[6]->setValue("FirstName");
        $input[7]->setValue("LastName");
        $input[8]->setValue("100");

        $this->assertNotNull($btn = $this->page->findAll('css', '.form-actions .btn'), 'Cannot find update button');
        $this->assertEquals(3, count($btn));

        $btn[1]->click();

        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(0, $contractWaiting, 'Count of waiting contracts should be 0 after update');

        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
    }
}
