<?php

namespace RentJeeves\LandlordBundle\Tests\Functional\Validator;

use RentJeeves\LandlordBundle\Validator\EmailExist;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class EmailExistValidatorCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function shouldNotValidateValueNotConvertedToString()
    {
        $userEmailExistConstraint = new EmailExist();
        $this->getContainer()->get('validator')->validateValue([], $userEmailExistConstraint);
    }

    /**
     * @test
     */
    public function shouldAddErrorIfFoundUser()
    {
        $userEmailExistConstraint = new EmailExist();
        $errorList = $this->getContainer()
            ->get('validator')
            ->validateValue('tenant11@example.com', $userEmailExistConstraint);

        $this->assertCount(1, $errorList);

        $this->assertEquals($userEmailExistConstraint->messageExist, $errorList[0]->getMessage());
    }

    /**
     * @test
     */
    public function shouldNotAddErrorIfNotFoundUser()
    {
        $userEmailExistConstraint = new EmailExist();
        $errorList = $this->getContainer()
            ->get('validator')
            ->validateValue('unique@email.com', $userEmailExistConstraint);

        $this->assertCount(0, $errorList);
    }
}
