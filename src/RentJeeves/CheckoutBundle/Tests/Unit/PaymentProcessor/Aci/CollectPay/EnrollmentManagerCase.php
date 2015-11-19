<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\CollectPay;

use CreditJeeves\DataBundle\Entity\Group;
use Payum\AciCollectPay\Model\Profile;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class EnrollmentManagerCase extends UnitTestBase
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

    /**
     * @return array
     */
    public function shouldTruncateBillingAccountNicknameTo45SymbolsDataProvider()
    {
        return [
            ['The Test Group With More Then Forty Symbols', '123456789101315'],
            ['The Test Group', '1234569888'],
            ['The Test Group 2', '12345678910131517192123252729313335373941434547'],
        ];
    }

    /**
     * @param string $groupName
     * @param string $merchantName
     *
     * @test
     * @dataProvider shouldTruncateBillingAccountNicknameTo45SymbolsDataProvider
     */
    public function shouldTruncateBillingAccountNicknameTo45Symbols($groupName, $merchantName)
    {
        // prepare fixtures
        $group = new Group();
        $group->setName($groupName) ; //'The Test Group With More Then Forty Symbols');
        $depositAccount = new DepositAccount();
        $depositAccount->setMerchantName($merchantName);//'123456789101315');
        $depositAccount->setGroup($group);

        // prepare class for testing
        $class = new \ReflectionClass('RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager');
        $enrollmentManager = $class->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager',
            'getBillingAccountNickname'
        );
        $method->setAccessible(true);

        $billingAccountNickname = $method->invoke($enrollmentManager, $depositAccount);

        $this->assertLessThanOrEqual(45, strlen($billingAccountNickname), 'String should be less or equal 45 symbols');
        $this->assertStringEndsWith(
            substr($depositAccount->getMerchantName(), 0 ,45),
            $billingAccountNickname,
            'Billing account nickname should have division id on the end'
        );
    }
}
