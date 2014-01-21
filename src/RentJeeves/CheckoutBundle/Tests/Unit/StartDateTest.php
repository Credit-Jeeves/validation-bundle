<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\CheckoutBundle\Constraint\StartDate;
use \DateTime;
use Symfony\Component\Validator\ExecutionContext;
use \Exception;

class StartDateTest extends BaseTestCase
{
    /**
     * @test
     */
    public function wrong()
    {
        $context = $this->getMock(
            'Symfony\Component\Validator\ExecutionContext',
            array(
                'addViolation'
            ),
            array(),
            '',
            false
        );

        $context->expects($this->any())
            ->method('addViolation')
            ->will($this->returnValue(null));

        $constraint = new StartDate();
        $constraint->oneTimeUntilValue = '18:00';
        $class = $constraint->validatedBy();
        $validator = new $class();
        $date = new DateTime();
        $date->modify("-1 month");
        $validator->initialize($context);
        $validator->validate($date->format('Y-m-d'), $constraint);
    }

    /**
     * @test
     */
    public function correct()
    {
        $context = $this->getMock(
            'Symfony\Component\Validator\ExecutionContext',
            array(
            ),
            array(),
            '',
            false
        );
        $context->expects($this->any())
            ->method('addViolation')
            ->will($this->throwException(new Exception));
        $constraint = new StartDate();
        $constraint->oneTimeUntilValue = '18:00';
        $class = $constraint->validatedBy();
        $validator = new $class();
        $date = new DateTime();
        $date->modify("+1 month");
        $validator->initialize($context);
        $validator->validate($date->format('Y-m-d'), $constraint);
    }
}
