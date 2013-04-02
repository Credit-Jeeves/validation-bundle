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
        $session = $this->getMink()->getSession('symfony');
        $session = $this->getMink()->getSession('symfony');
        $session->visit($this->getUrl());
    }
}
