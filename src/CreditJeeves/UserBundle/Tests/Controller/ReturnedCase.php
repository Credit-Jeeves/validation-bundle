<?php
namespace CreditJeeves\UserBundle\Tests\Controller;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

class ReturnedCase extends BaseTestCase
{
    /**
     * @test
     */
    public function userRemoveData()
    {
        $this->markTestSkipped('We no longer support CJ. Therefore, this test can be skipped');
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->login('emilio@example.com', 'pass');
        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('settings.remove');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->fillForm(
            $form,
            array(
                'remove_password' => 'pass'
            )
        );
        $form->pressButton('common.remove');
        $this->assertNotNull($messageBody = $this->page->find('css', '.message-body'));
        $this->assertEquals('authorization.description.removed', $messageBody->getText());
    }

    /**
     * @test
     * @depends userRemoveData
     */
    public function userReturned()
    {
        $this->login('emilio@example.com', 'pass');
        $this->assertNotNull($form = $this->page->find('css', '#creditjeeves_applicantbundle_leadreturnedtype'));
        $form->pressButton('common.get.score');
        $this->assertCount(7, $this->page->findAll('css', '.error_list li'));
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_leadreturnedtype_code' => 'DVRWP2NFQ6',
                'creditjeeves_applicantbundle_leadreturnedtype_user_ssn_ssn1' => '666',
                'creditjeeves_applicantbundle_leadreturnedtype_user_ssn_ssn2' => '81',
                'creditjeeves_applicantbundle_leadreturnedtype_user_ssn_ssn3' => '0987',
                'creditjeeves_applicantbundle_leadreturnedtype_user_addresses_0_street' => 'SAINT NAZAIRE 2010',
                'creditjeeves_applicantbundle_leadreturnedtype_user_addresses_0_unit' => '116TH 1',
                'creditjeeves_applicantbundle_leadreturnedtype_user_addresses_0_city' => 'HOMESTEAD',
                'creditjeeves_applicantbundle_leadreturnedtype_user_addresses_0_area' => 'FL',
                'creditjeeves_applicantbundle_leadreturnedtype_user_addresses_0_zip' => '33039',
                'creditjeeves_applicantbundle_leadreturnedtype_user_phone' => '7188491319',
                'creditjeeves_applicantbundle_leadreturnedtype_user_date_of_birth_day' => '19',
                'creditjeeves_applicantbundle_leadreturnedtype_user_date_of_birth_month' => '02',
                'creditjeeves_applicantbundle_leadreturnedtype_user_date_of_birth_year' => '1957',
                'creditjeeves_applicantbundle_leadreturnedtype_user_tos' => true,
            )
        );
        $form->pressButton('common.get.score');

        $this->assertNotNull($form = $this->page->find('css', '#id_pidkiq_page'));
        $this->logout();
    }
}
