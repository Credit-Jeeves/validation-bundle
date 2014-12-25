<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

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
        $this->assertCount(1, $contractWaiting);

        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_contract_waiting'));

        $tableBlock->clickLink('link_list');
        $this->assertNotNull($create = $this->page->find('css', '.sonata-action-element'));
        $create->click();
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->session->wait(
            10000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            15000,
            "$('.overlay-trigger').length <= 0"
        );

        $this->assertNotNull($input = $this->page->findAll('css', 'input'));
        $this->assertEquals(10, count($input));
        $input[0]->setValue("200");
        $input[1]->setValue("615");
        $input[2]->setValue("test@mail.com");
        $input[3]->setValue("FirstName");
        $input[4]->setValue("LastName");
        $input[5]->setValue("100");

        $this->assertNotNull($btn = $this->page->findAll('css', '.form-actions .btn'));
        $this->assertEquals(3, count($btn));

        $btn[1]->click();
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractWaiting);

        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
    }
}
