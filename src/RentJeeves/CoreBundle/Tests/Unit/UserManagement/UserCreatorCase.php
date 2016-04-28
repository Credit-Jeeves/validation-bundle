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
     * @expectedException \RentJeeves\CoreBundle\Exception\UserCreatorException
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
        $userRepository = $this->getBaseMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('getLastUserNameByPartOfUserName')
            ->willReturn($inputUserName);

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
            ->method('getLastUserNameByPartOfUserName')
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

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\UserCreatorException
     * @expectedExceptionMessage User with email "test@test.com" already exist.
     */
    public function shouldThrowExceptionIfEmailAlreadyExistsForCreateTenantWithEmail()
    {
        $userRepository = $this->getBaseMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(new Tenant());

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('DataBundle:User'))
            ->willReturn($userRepository);
        $em->expects($this->never())
            ->method('persist');

        $validator = $this->getValidatorMock();

        $manager = new UserCreator(
            $em,
            $this->getLoggerMock(),
            $validator
        );
        $manager->createTenant('test', 'test', 'test@test.com');
    }

    /**
     * @test
     */
    public function shouldJustCreateTenantForCreateTenantWithEmail()
    {
        $userRepository = $this->getBaseMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
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
                $this->assertEquals('test@test.com', $subject->getUsername(), 'Incorrect Username.');
                $this->assertEquals('test@test.com', $subject->getUsernameCanonical(), 'Incorrect UsernameCanonical.');
                $this->assertEquals('test@test.com', $subject->getEmail(), 'Incorrect Email.');
                $this->assertEquals('test@test.com', $subject->getEmailCanonical(), 'Incorrect EmailCanonical.');
                $this->assertTrue($subject->getEmailNotification(), 'EmailNotification should be true.');
                $this->assertTrue($subject->getOfferNotification(), 'OfferNotification should be true.');

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
        $manager->createTenant('test', 'test', 'test@test.com');
    }
}
