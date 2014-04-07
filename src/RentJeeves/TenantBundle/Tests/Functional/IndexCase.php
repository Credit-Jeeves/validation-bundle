<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class IndexCase extends BaseTestCase
{
    /**
     * @test
     */
    public function paymentsHistory()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($paymentsTable = $this->page->find('css', '#tenant-payments'));
        $this->assertNotNull($payments = $this->page->findAll('css', '#tenant-payments table>tbody>tr'));
        $this->assertEquals(36, count($payments));
    }
} 
