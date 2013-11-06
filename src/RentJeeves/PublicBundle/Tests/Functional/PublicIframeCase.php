<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class PublicIframeCase extends BaseTestCase
{
    protected $timeout = 30000;

    /**
     * @test
     */
    public function loginViaIframe()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'management');
        $this->assertNotNull($form = $this->page->find('css', '#iframe-login-form form'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_logintype_email'    => 'tenant11@example.com',
                'rentjeeves_publicbundle_logintype_password' => 'pass',
            )
        );
        $this->assertNotNull($submit = $this->page->find('css', '#rentjeeves_publicbundle_logintype_save'));
        $submit->click();
        $this->session->wait($this->timeout, "$('.properties-table>tbody>tr').children().length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(3, $tr, 'List of property');
        $this->logout();
    }
}
