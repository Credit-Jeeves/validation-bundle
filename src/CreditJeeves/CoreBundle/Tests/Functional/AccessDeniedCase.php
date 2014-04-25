<?php

namespace CreditJeeves\CoreBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

class AccessDeniedCase extends BaseTestCase
{
    /**
     * @test
     */
    public function onKernelException()
    {
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableTr = $this->page->find('css', '#id_block_emails'));
        $this->session->visit($this->getUrl());
        $this->assertNotNull($tableTr = $this->page->find('css', '#id_block_emails'));
    }
}
