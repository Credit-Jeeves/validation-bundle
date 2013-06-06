<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\TestBundle\BaseTestCase;
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
            self::getContainer()->getParameter('experian.atb')
        );
        $result = $atb->increaseScoreByX(
            new ArfParser($data),
            40,
            'NGaFhYYDL+69QzaNMt1CGRQVVHn6bS9X1Pb5Mj9STP7bAYtxqzzM1131bpk3\nicy26YjqrpzMRSGs73qWNpUdTNu+dNLcJWYMrl3' .
            'h2PbT9dx+42fnTXvAfpLp\nfvJNs0txMOKdBD2xRL45/Ox+8Lik8RTuO7cMd/z12lHBpHU6AQGSm/vEpUwA\nqms5YKSX7Zc4\n'
        );
        $this->assertCount(1, $result['blocks']);
        $this->assertArrayHasKey('banks', $result['blocks'][0]);
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
            self::getContainer()->getParameter('experian.atb')
        );
        $result = $atb->increaseScoreByX(
            new ArfParser($data),
            200,
            "NGaFhYYDL+69QzaNMt1CGRQVVHn6bS9X1Pb5Mj9STP7bAYtxqzzM1131bpk3\nicy26YjqrpzMRSGs73qWNpUdTNu+dNLcJWYMrl3" .
            "h2PbT9dx+42fnTXvAfpLp\nfvJNs0txMOKdBD2xRL45/Ox+8Lik8RTuO7cMd/z12lHBpHU6AQGSm/vEpUwA\nqms5YKSX7Zc4\n"
        );
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
            self::getContainer()->getParameter('experian.atb')
        );
        $result = $atb->bestUseOfCash(
            new ArfParser($data),
            100,
            "NGaFhYYDL+69QzaNMt1CGRQVVHn6bS9X1Pb5Mj9STP7bAYtxqzzM1131bpk3\nicy26YjqrpzMRSGs73qWNpUdTNu+dNLcJWYMrl3" .
            "h2PbT9dx+42fnTXvAfpLp\nfvJNs0txMOKdBD2xRL45/Ox+8Lik8RTuO7cMd/z12lHBpHU6AQGSm/vEpUwA\nqms5YKSX7Zc4\n"
        );

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
            self::getContainer()->getParameter('experian.atb')
        );
        $result = $atb->bestUseOfCash(
            new ArfParser($data),
            40,
            "NGaFhYYDL+69QzaNMt1CGRQVVHn6bS9X1Pb5Mj9STP7bAYtxqzzM1131bpk3\nicy26YjqrpzMRSGs73qWNpUdTPI/U8MLfOhcyoYbx" .
            "2HHAjErKY04E/hFNO9C\nY8EuqbQ98E5eOI2soQZ8aTdBE4ZyGQe3xxA0fB4jhN2XDUaQ/AZsCY/iRKng\nS4MwCa+zYrcd\n"
        );
        $this->assertEquals('None of 6 Best Use of Cash simulations can achieve the target score.', $result['message']);
    }
}
