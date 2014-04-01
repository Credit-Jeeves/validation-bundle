<?php
namespace RentJeeves\DataBundle\Tests\Entity;

use \DateTime;
use \PHPUnit_Framework_TestCase;
use RentJeeves\DataBundle\Entity\Payment;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class PaymentCase extends PHPUnit_Framework_TestCase
{
    public function providerForgetNextPaymentDate()
    {
        return array(
            array(31, '2014-01-15', '2014-01-31'),
            array(30, '2014-02-27', '2014-02-28'),
            array(1, '2014-02-27', '2014-03-01'),
            array(1, '2014-03-31', '2014-04-01'),
        );
    }
    public function getNextPaymentDate($dueDate, $now, $will)
    {
        /** @var Payment $payment */
        $payment = $this->getMock(
            '\RentJeeves\DataBundle\Entity\Payment',
            array('getNow'),
            array(),
            '',
            false
        );
        $payment->expects($this->once())
            ->method('getNow')
            ->will($this->returnValue(new DateTime($now)));
        $payment->setDueDate($dueDate);

        $this->assertEquals(new DateTime($will), $payment->getNextPaymentDate());
    }

    /**
     * @test
     * @dataProvider providerForgetNextPaymentDate
     */
    public function getNextPaymentDateWithDifferentTimezones($dueDate, $now, $will)
    {
        \date_default_timezone_set('Europe/Kiev');
        $this->getNextPaymentDate($dueDate, $now, $will);
        \date_default_timezone_set('America/New_York');
        $this->getNextPaymentDate($dueDate, $now, $will);
        \date_default_timezone_set('GMT');
        $this->getNextPaymentDate($dueDate, $now, $will);
    }
}
