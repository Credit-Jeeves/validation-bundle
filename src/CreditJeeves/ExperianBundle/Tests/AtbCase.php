<?php
namespace CreditJeeves\ExperianBundle\Tests;

use \stdClass;
use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\ExperianBundle\Atb;

/**
 * ATB test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class AtbCase extends \CreditJeeves\TestBundle\BaseTestCase
{

    private $blocksHead = array(
        'transaction_signature' => '...',
        'score_init' => 0,
        'score_best' => 0,
        'cash_used' => 0,
        'sim_type' => 0,
        'message' => '',
        'blocks' => array(),
    );

    private $blockExample = array(
        'tr_rev_install' => 'I',
        'tr_amount1' => '1',
        'tr_amount1_qual' => 'L',
        'tr_amount1' => '100',
        'tr_amount1_qual' => 'H',
        'tr_balance' => '50',
        'tr_subcode' => '1',
        'tr_acctnum' => '6789',
        'tr_subname' => 'BANK NAME',
        'arf_balance' => '50',
    );

    /**
     * @param array $tradeLines
     *
     * @return Atb
     */
    protected function getMockAtb($tradeLines = array())
    {
        if (empty($tradeLines)) {
            $tradeLines = array($this->blockExample);
        }
        $atb = $this->getMock(
            'CreditJeeves\ExperianBundle\Atb',
            array('getTradeLines', 'getDirectCheck'),
            array(),
            '',
            false
        );

        $atb->expects($this->any())
            ->method('getTradeLines')
            ->withAnyParameters()
            ->will($this->returnValue($tradeLines));
        $atb->expects($this->any())
            ->method('getDirectCheck')
            ->withAnyParameters()
            ->will($this->returnValue(array()));

        return $atb;
    }

    /**
     * @test
     * @expectedException \CreditJeeves\ExperianBundle\Exception\Atb
     * @expectedExceptionMessage Unsupported type '0'
     */
    public function composeBlocksError()
    {
        $atb = $this->getMockAtb();
        $atb->composeBlocks(array('sim_type' => 0, 'blocks' => array(array('tr_acctnum' => '123', 'tr_subcode' => 0))));

    }

    /**
     * Generates blocks
     *
     * @param $type
     *
     * @return array
     */
    private function getBlocks($type)
    {
        $blocks = $this->blocksHead;
        $blocks['sim_type'] = $type;
        $block = $this->blockExample;
        $block['tr_balance'] = 41;
        $block['subscriber_phone_number'] = '';
        $blocks['blocks'] = array($block);

        return $blocks;
    }

    /**
     * @test
     * @expectedException \CreditJeeves\ExperianBundle\Exception\Atb
     * @expectedExceptionMessage Data was missed
     */
    public function composeBlocks10xError()
    {
        $atb = $this->getMockAtb();
        $blocks = $this->getBlocks(101);
        $blocks['blocks'][0]['tr_balance'] = 60;
        $atb->composeBlocks($blocks);
    }

    private $blocksCounter = 0;

    /**
     * Blocks of composeBlocks
     *
     * @return array
     */
    public function blocks()
    {

        $this->blocksCounter += 100;
        $blocks = $this->getBlocks($this->blocksCounter);
        $params = array($blocks, $blocks);

        return array(
            $params,
            $params,
            $params,
            $params,
        );
    }

    /**
     * @test
     * @dataProvider blocks
     */
    public function composeBlocks($expected, $blocks)
    {
        $atb = $this->getMockAtb();
        $this->assertEquals($expected, $atb->composeBlocks($blocks));
    }

    /**
     * @test
     */
    public function composeBlocks300SecondCondition()
    {
        $atb = $this->getMockAtb();
        $blocks = $this->getBlocks(300);
        $blocks['blocks'][0]['tr_balance'] = 0;
        $block = $this->blockExample;
        $block['tr_subname'] = 'NEW ACCOUNT';
        $block2 = $this->blockExample;
        $block2['tr_subname'] = 'SOME OTHER BANK';
        $blocks['blocks'] = array_merge($blocks['blocks'], array($block, $block2));

        $result = $atb->composeBlocks($blocks);
        $this->assertEquals(
            array(
                'banks' => 'BANK NAME, SOME OTHER BANK',
                'tr_balance' => 50
            ),
            $result['blocks'][0]
        );
    }

    /**
     * @test
     * @expectedException \CreditJeeves\ExperianBundle\Exception\Atb
     * @expectedExceptionMessage Data was missed
     */
    public function composeBlocks300SecondConditionError()
    {
        $atb = $this->getMockAtb();
        $blocks = $this->getBlocks(300);
        $blocks['blocks'][0]['tr_balance'] = 0;
        $atb->composeBlocks($blocks);
    }

    /**
     * @test
     */
    public function composeBlocksSortOrder()
    {
        $blocks = array(
            6 => array(
                'tr_subcode' => 1,
                'tr_acctnum' => 1,
                'tr_status' => 1,
                'tr_balance' => 100,
            ),
            4 => array(
                'tr_subcode' => 1,
                'tr_acctnum' => 2,
                'tr_status' => 72,
                'tr_balance' => 100,
            ),
            5 => array(
                'tr_subcode' => 1,
                'tr_acctnum' => 3,
                'tr_status' => 73,
                'tr_balance' => 100,
            ),
            2 => array(
                'tr_subcode' => 2,
                'tr_acctnum' => 2,
                'tr_status' => 78,
                'tr_balance' => 100,
            ),
            3 => array(
                'tr_subcode' => 2,
                'tr_acctnum' => 3,
                'tr_status' => 79,
                'tr_balance' => 90,
            ),
            1 => array(
                'tr_subcode' => 3,
                'tr_acctnum' => 2,
                'tr_status' => 81,
                'tr_balance' => 99,
            ),
            0 => array(
                'tr_subcode' => 3,
                'tr_acctnum' => 3,
                'tr_status' => 82,
                'tr_balance' => 100,
            ),
        );

        $atb = $this->getMockAtb($blocks);
        $res = $atb->composeBlocks(
            array(
                'sim_type' => 200,
                'blocks' => $blocks
            )
        );
        ksort($blocks);
        $this->assertEquals($blocks, $res['blocks']);

        $i = 0;
        $count = count($blocks);
        foreach ($res['blocks'] as $block) {
            $i++;
            $this->assertEquals(current($blocks), $block, "Block {$i} from {$count} is incorrect");
            next($blocks);
        }
    }

    /**
     * Tests Atb->parseATBResponse()
     *
     * @test
     */
    public function parseATBResponse()
    {
        $atb = $this->getMockAtb();
        $obj = new stdClass();
        $obj->code = 0;
        $obj->result = file_get_contents(self::getContainer()->getParameter('data.dir') . '/experian/atb/Response.xml');

        $result = $atb->parseATBResponse($obj);
        $this->assertEquals('536', $result['score_init']);
        $this->assertEquals('647', $result['score_best']);
        $this->assertEmpty($result['message']);
        $this->assertEquals(
            array(
                'tr_amount1' => '900',
                'tr_balance' => '0',
                'tr_acctnum' => '4120618009111234',
                'tr_subname' => 'MERRICK BANK',
                'tr_rev_install' => 'R',
                'tr_amount2' => '1048',
                'tr_amount2_qual' => 'H',
                'tr_amount1_qual' => 'L',
                'tr_subcode' => '0206610',
                'subscriber_phone_number' => '',
            ),
            $result['blocks'][0]
        );
    }

    /**
     * Tests Atb->parseATBResponse()
     *
     * @test
     */
    public function parseATBResponse2()
    {
        $atb = $this->getMockAtb();

        $obj = new stdClass();
        $obj->code = 0;
        $obj->result = file_get_contents(
            self::getContainer()->getParameter('data.dir') . '/experian/atb/Response2.xml'
        );

        $result = $atb->parseATBResponse($obj);
        $this->assertEquals('536', $result['score_init']);
        $this->assertEquals('589', $result['score_best']);
        $this->assertEmpty($result['message']);
        $this->assertEquals(
            array(
                'tr_amount1' => '400',
                'tr_amount1_qual' => 'L',
                'tr_balance' => '356',
                'tr_acctnum' => '5178007092321234',
                'tr_subname' => 'FIRST PREMIER BANK',
                'tr_amount2' => '477',
                'tr_amount2_qual' => 'H',
                'tr_rev_install' => 'R',
                'tr_subcode' => '1210189',
                'subscriber_phone_number' => '',
            ),
            $result['blocks'][0]
        );
    }

    /**
     * Tests Atb->parseATBResponse()
     *
     * @test
     */
    public function parseATBResponseError()
    {
        $atb = $this->getMockAtb();

        $obj = new stdClass();
        $obj->code = 0;
        $obj->result = file_get_contents(self::getContainer()->getParameter('data.dir') . '/experian/atb/Error.xml');

        $result = $atb->parseATBResponse($obj);

        $this->assertEquals(
            "Initial Score is greater than ScoreMax. guNo simulation is\n" .
            "    available by IncreaseScoreByX_FULL.",
            $result['message']
        );
        $this->assertTrue(empty($result['blocks']));
    }

    /**
     * Tests Atb->parseATBResponse()
     *
     * @test
     */
    public function parseATBResponseEmpty()
    {
        $atb = $this->getMockAtb();

        $obj = new stdClass();
        $obj->code = 0;
        $obj->result = file_get_contents(self::getContainer()->getParameter('data.dir') . '/experian/atb/Empty.xml');
        $result = $atb->parseATBResponse($obj);

        $this->assertEquals(
            array(
                'blocks' => array(),
                'score_best' => 0,
                'score_init' => 0,
            ),
            $result
        );
    }
}
