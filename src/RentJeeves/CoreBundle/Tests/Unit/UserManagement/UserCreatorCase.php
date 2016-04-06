<?php

namespace RentJeeves\CoreBundle\Tests\Unit\UserManagement;

use CreditJeeves\DataBundle\Entity\UserRepository;
use RentJeeves\CoreBundle\UserManagement\UserCreator;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use Symfony\Component\Validator\ConstraintViolationList;

class UserCreatorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfInputDataIsEmptyForCreateTenantWithoutEmail()
    {
        $manager = new UserCreator(
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $this->getValidatorMock()
        );
        $manager->createTenant('', '');
    }

    /**
     * @return array
     */
    public function providerForCreateTenantWithoutEmail()
    {
        return [
            ['test', 'test1'],
            ['test20', 'test21'],
        ];
    }

    /**
     * @param string $inputUserName
     * @param string $outputUserName
     *
     * @test
     * @dataProvider providerForCreateTenantWithoutEmail
     */
    public function shouldAddDigitForUserNameIfUserNameIsExistForCreateTenantWithoutEmail(
        $inputUserName,
        $outputUserName
    ) {
        $user = new Tenant();
        $user->setUsernameCanonical($inputUserName);

        $userRepository = $this->getBaseMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findLastByPartOfUserName')
            ->willReturn($user);

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('DataBundle:User'))
            ->willReturn($userRepository);
        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($subject) use ($outputUserName) {
                /** @var Tenant $subject */
                $this->assertInstanceOf(Tenant::class, $subject, 'Persisted incorrect object.');
                $this->assertEquals($outputUserName, $subject->getUsernameCanonical(), 'Incorrect userName.');

                return true;
            }));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $manager = new UserCreator(
            $em,
            $this->getLoggerMock(),
            $validator
        );
        $manager->createTenant('te', 'st');
    }

    /**
     * @test
     */
    public function shouldJustCreateTenantForCreateTenantWithoutEmail()
    {
        $userRepository = $this->getBaseMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findLastByPartOfUserName')
            ->willReturn(null);

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('DataBundle:User'))
            ->willReturn($userRepository);
        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($subject) {
                /** @var Tenant $subject */
                $this->assertInstanceOf(Tenant::class, $subject, 'Persisted incorrect object.');
                $this->assertEquals('test', $subject->getUsernameCanonical(), 'Incorrect userName.');

                return true;
            }));

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $manager = new UserCreator(
            $em,
            $this->getLoggerMock(),
            $validator
        );
        $manager->createTenant('te', 'st');
    }
}
