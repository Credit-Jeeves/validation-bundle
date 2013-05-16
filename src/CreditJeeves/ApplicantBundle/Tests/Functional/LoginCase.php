<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Tests\Functional\BaseTestCase;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class LoginCase extends BaseTestCase
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
    );

    /**
     * @test
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function userCanLogin()
    {
        $this->load($this->fixtures, true);
//        $this->setDefaultSession('zombie');
        $this->login('emilio@example.com', 'pass');
        $this->logout();
    }

    /**
     * @test
     * @depends userCanLogin
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function wrongPassword()
    {
        $this->login('emilio@example.com', '123');
    }
}
