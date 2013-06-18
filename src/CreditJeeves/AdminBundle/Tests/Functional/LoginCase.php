<?php
namespace CreditJeeves\AdminBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class LoginCase extends \CreditJeeves\TestBundle\Functional\BaseTestCase
{
    protected $fixtures = array(
        '001_cj_account_group.yml',
        '002_cj_admin_account.yml',
        '003_cj_dealer_account.yml',
        '004_cj_applicant.yml',
        '005_cj_lead.yml',
        '006_cj_applicant_report.yml',
        '007_cj_applicant_score.yml',
        '010_cj_affiliate.yml',
        '013_cj_holding_account.yml',
        '020_email.yml',
        '021_email_translations.yml',
    );

    /**
     * @test
     */
    public function userCanLogin()
    {
        $this->load($this->fixtures, true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->logout();
    }

    /**
     * @test
     * @depends userCanLogin
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function wrongPassword()
    {
        $this->login('admin@example.com', '123');
    }
}
