<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\CoreBundle\Tests\BaseTestCase;
use CreditJeeves\ExperianBundle\Atb;

/**
 * ATB test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class AtbCase extends BaseTestCase
{
    /**
     * Tests Atb->increaseScoreByX()
     *
     * @test
     */
    public function increaseScoreByX()
    {
        $data = file_get_contents(
            self::getContainer()->getParameter('data.dir') . '/experian/netConnect/Response2.arf'
        );
        $atb = new Atb(
            new ArfParser($data),
            self::getContainer()->getParameter('experian.atb')
        );
        $result = $atb->increaseScoreByX(40);
        $this->assertCount(13, $result['blocks']);
    }

    /**
     * Tests Atb->increaseScoreByX()
     * @depends increaseScoreByX
     * @test
     */
    public function increaseScoreByXError()
    {
        $data = file_get_contents(
            self::getContainer()->getParameter('data.dir') . '/experian/netConnect/Response2.arf'
        );
        $atb = new Atb(
            new ArfParser($data),
            self::getContainer()->getParameter('experian.atb')
        );
        $result = $atb->increaseScoreByX(200);
        $this->assertTrue(empty($result['blocks']));
        $this->assertEquals(
            'None of the simulations can achieve the target score in IncreaseScoreByX_FULL.',
            $result['message']
        );
    }

    /**
     * Tests Atb->bestUseOfCash()
     *
     * @test
     */
    public function bestUseOfCash()
    {
        $data = file_get_contents(
            self::getContainer()->getParameter('data.dir') . '/experian/netConnect/Response2.arf'
        );
        $atb = new Atb(
            new ArfParser($data),
            self::getContainer()->getParameter('experian.atb')
        );
        $result = $atb->bestUseOfCash(100);
        $this->assertCount(3, $result['blocks']);
    }

    /**
     * Tests Atb->bestUseOfCash()
     * @depends bestUseOfCash
     * @test
     */
    public function bestUseOfCashError()
    {
        $data = file_get_contents(self::getContainer()->getParameter('data.dir') . '/experian/netConnect/Response.arf');
        $atb = new Atb(
            new ArfParser($data),
            self::getContainer()->getParameter('experian.atb')
        );
        $result = $atb->bestUseOfCash(40);
        $this->assertEquals('None of 6 Best Use of Cash simulations can achieve the target score.', $result['message']);
    }
}
