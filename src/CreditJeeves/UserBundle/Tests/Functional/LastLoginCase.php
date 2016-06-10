<?php
namespace CreditJeeves\UserBundle\Tests\Functional;

use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\UserBundle\EventListener\LastLogin;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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

    /**
     * @test
     */
    public function shouldSendEmailWhenTenantFirstLogin()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var Tenant $user */
        $user = $em->getRepository('RjDataBundle:Tenant')->findOneBy(array('email' => 'roger@rentrack.com'));
        $this->assertEquals(UserType::TENANT, $user->getType(), 'User should be tenant');
        $this->assertEmpty($user->getLastLogin(), 'Last login should be null from user. Please,check fixtures');

        $requestMock = $this->getMock(Request::class);
        $requestMock->expects($this->once())
            ->method('getClientIp')
            ->willReturn('127.1.1.1');
        $tokenMock = $this->getMock(TokenInterface::class);
        $tokenMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $eventListener = new LastLogin($em, $this->getContainer()->get('project.mailer'));
        $event = new InteractiveLoginEvent($requestMock, $tokenMock);

        $emailPlugin = $this->registerEmailListener();
        $emailPlugin->clean();

        $eventListener->onSecurityInteractiveLogin($event);

        $this->assertCount(1, $emailPlugin->getPreSendMessages());
        $this->assertEquals('Welcome to RentTrack', $emailPlugin->getPreSendMessage(0)->getSubject());
        $this->logout();
        $this->getEmailReader()->clear();
    }
}
