<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class IndexCase extends BaseTestCase
{
    /**
     * @test
     */
    public function existPaymentsHistory()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($paymentsTable = $this->page->find('css', '#tenant-payments'));
        $this->assertNotNull($payments = $this->page->findAll('css', '#tenant-payments table>tbody>tr'));
        $this->assertEquals(36, count($payments));
        $this->logout();
    }

    /**
     * @test
     */
    public function emptyPaymentsHistory()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('mamazza@rentrack.com', 'pass');
        $this->assertNotNull($paymentsTable = $this->page->find('css', '#tenant-payments'));
        $this->assertFalse($paymentsTable->isVisible());
        $this->assertEquals(0, count($this->page->findAll('css', '#tenant-payments table>tbody>tr')));
    }
}
