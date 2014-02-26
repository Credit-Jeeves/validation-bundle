<?php

use CreditJeeves\TestBundle\Functional\BaseTestCase;

class AccessDeniedCase extends BaseTestCase
{
    /**
     * @test
     */
    public function onKernelException()
    {
        $this->load(true);
        $this->login('john@example.com', 'pass');
        $this->session->visit($this->getUrl().'admin');
        $this->assertNotNull($title = $this->page->find('css', 'h1'));
        $this->assertNotNull($description = $this->page->find('css', '.message-body'));
        $this->assertEquals('access.denied', $title->getText());
        $this->assertEquals('access.denied.description', $description->getText());
    }
}
