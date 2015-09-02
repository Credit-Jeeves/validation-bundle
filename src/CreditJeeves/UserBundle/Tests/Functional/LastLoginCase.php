<?php
namespace CreditJeeves\UserBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class LastLoginCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSetLastIpAndDateWhenUserLogsIn()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $user = $em->getRepository('DataBundle:User')->findOneBy(array('username' => 'landlord1@example.com'));
        $lastLoginBefore = $user->getLastLogin();
        $em->clear();
        $this->login('landlord1@example.com', 'pass');
        $this->logout();
        $userAfterLogin = $em->getRepository('DataBundle:User')->findOneBy(['username' => 'landlord1@example.com']);
        $lastLoginAfter = $userAfterLogin->getLastLogin();
        $ip = $userAfterLogin->getLastIp();
        $this->assertTrue($lastLoginBefore < $lastLoginAfter, "The last login timestamp did not advance");
        $validIp = filter_var($ip, FILTER_VALIDATE_IP);
        $this->assertNotEquals(false, $validIp, "The last login IP address did not update correctly");
    }
}
