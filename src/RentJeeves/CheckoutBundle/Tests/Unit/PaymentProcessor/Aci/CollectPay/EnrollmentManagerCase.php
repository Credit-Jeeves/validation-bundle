<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\CollectPay;

use CreditJeeves\DataBundle\Entity\Group;
use Payum\AciCollectPay\Model\Profile;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class EnrollmentManagerCase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

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

        $group = new Group();
        $this->writeIdAttribute($group, 2002);

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
