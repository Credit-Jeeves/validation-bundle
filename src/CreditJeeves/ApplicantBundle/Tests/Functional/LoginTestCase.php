<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Tests\Functional\BaseTestCase;
/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class LoginTestCanse extends BaseTestCase
{
    /**
     * @test
     */
    public function userCanLogin()
    {
        die('OK');
        $session = $this->getMink()->getSession();
        $session->visit($this->getUrl());
        sleep(900);
    }
}
