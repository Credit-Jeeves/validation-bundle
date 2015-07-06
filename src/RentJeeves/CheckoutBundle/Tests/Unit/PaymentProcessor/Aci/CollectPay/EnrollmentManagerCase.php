<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\CollectPay;

use Payum\AciCollectPay\Model\Profile;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\TestBundle\BaseTestCase;

class EnrollmentManagerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldPrepareGroupProfileWithUniqueUsername()
    {
        $user = new Landlord();
        $user->setFirstName('Adam');
        $user->setLastName('Smith');
        $user->setEmail('adam.smith@renttrack.com');
        $user->setPhone('0879888745');

        // can't use new instance of Group - there is no setId() method
        $group = $this->getMock('CreditJeeves\DataBundle\Entity\Group', [], [], '', false);
        $group->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2002));

        $class = new \ReflectionClass('RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager');
        $enrollmentManager = $class->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager',
            'prepareGroupProfile'
        );
        $method->setAccessible(true);

        /** @var Profile $profile */
        $profile = $method->invoke($enrollmentManager, $user, $group);
        $profileUser = $profile->getUser();
        $this->assertEquals(md5('G2002'), $profileUser->getUsername(), 'Group profile username is incorrect');
        $this->assertEquals('Adam Smith', $profileUser->getName(), 'Group profile name is incorrect');
        $this->assertEquals('adam.smith@renttrack.com', $profileUser->getEmail(), 'Group profile email is incorrect');
        $this->assertEquals('0879888745', $profileUser->getPhone(), 'Group profile phone is incorrect');
    }
}
