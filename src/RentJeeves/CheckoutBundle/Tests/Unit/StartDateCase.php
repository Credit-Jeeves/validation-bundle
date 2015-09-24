<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use RentJeeves\CheckoutBundle\Constraint\StartDateValidator;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\CheckoutBundle\Constraint\StartDate;

class StartDateCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \Exception
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
            ->will($this->throwException(new \Exception()));

        $constraint = new StartDate();
        $constraint->oneTimeUntilValue = '18:00';
        $class = $constraint->validatedBy();
        $validator = new $class();
        $date = new \DateTime();
        $date->modify("-1 month");
        $validator->initialize($context);
        $validator->validate($date->format('Y-m-d'), $constraint);
    }

    /**
     * @test
     */
    public function correctPlusMonth()
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
            ->will($this->throwException(new \Exception()));
        $constraint = new StartDate();
        $constraint->oneTimeUntilValue = '18:00';
        $class = $constraint->validatedBy();
        $validator = new $class();
        $date = new \DateTime();
        $date->modify("+1 month");
        $validator->initialize($context);
        $validator->validate($date->format('Y-m-d'), $constraint);
    }

    /**
     * @test
     */
    public function correctToday()
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
            ->will($this->throwException(new \Exception()));
        $constraint = new StartDate();
        $constraint->oneTimeUntilValue = '23:59';
        $class = $constraint->validatedBy();
        $validator = new $class();
        $date = new \DateTime();
        $validator->initialize($context);
        $validator->validate($date->format('Y-m-d'), $constraint);
    }

    /**
     * @return array
     */
    public function isPastCutoffTimeDataProvider()
    {
        return [
            [new \DateTime('10:18'), '10:20', false],
            [new \DateTime('10:20'), '10:20', true],
            [new \DateTime('18:21'), '18:20', true]
        ];
    }

    /**
     * @param \DateTime $dateValidation
     * @param string $oneTimeUntilValue
     * @param bool $result
     *
     * @test
     * @dataProvider isPastCutoffTimeDataProvider
     */
    public function isPastCutoffTime(\DateTime $dateValidation, $oneTimeUntilValue, $result)
    {
        $this->assertTrue(
            $result === StartDateValidator::isPastCutoffTime($dateValidation, $oneTimeUntilValue),
            'Invalid calculation for cutoff time'
        );
    }
}
