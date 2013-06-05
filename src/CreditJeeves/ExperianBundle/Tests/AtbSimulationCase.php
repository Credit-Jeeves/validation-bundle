<?php
namespace CreditJeeves\ExperianBundle\Tests;

use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\ExperianBundle\AtbSimulation;
use CreditJeeves\DataBundle\Enum\AtbType;
use CreditJeeves\ExperianBundle\Model\Atb as Model;

class AtbSimulationCase extends \CreditJeeves\TestBundle\BaseTestCase
{
    /**
     * @test
     */
    public function itMustBeConstructed()
    {
        $atb = $this->getMock('CreditJeeves\ExperianBundle\Atb', array(), array(), '', false);
        $converter = $this->getMock('CreditJeeves\ExperianBundle\Converter\Atb', array(), array(), '', false);
        $em = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $serializer = $this->getMock('JMS\Serializer\Serializer', array(), array(), '', false);
        new AtbSimulation($atb, $converter, $em, $serializer);
    }

    protected function getMockAtbSimulation()
    {
        $atbSimulation = $this->getMock(
            'CreditJeeves\ExperianBundle\AtbSimulation',
            array(
                'getAtb',
                'getConverter',
                'getEM',
                'findLastSimulationEntity',
            ),
            array(),
            '',
            false
        );

        $atb = $this->getMock(
            'CreditJeeves\ExperianBundle\Atb',
            array(
                'bestUseOfCash'
            ),
            array(),
            '',
            false
        );

        $atb->expects($this->once())
            ->method('bestUseOfCash')
            ->will(
                $this->returnValue(
                    array(
                        'sim_type' => 100,
                        'transaction_signature' => 'abc',
                    )
                )
            );

        $atbSimulation->expects($this->once())
            ->method('getAtb')
            ->will($this->returnValue($atb));

        $converter = $this->getMock(
            'CreditJeeves\ExperianBundle\Converter\Atb',
            array(
                'getModel'
            ),
            array(),
            '',
            false
        );

        $converter->expects($this->once())
            ->method('getModel')
            ->will($this->returnValue(new Model()));

        $atbSimulation->expects($this->once())
            ->method('getConverter')
            ->will($this->returnValue($converter));

        $em = $this->getMock(
            'Doctrine\ORM\EntityManager',
            array(
                'persist',
                'flush',
            ),
            array(),
            '',
            false
        );

        $em->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(true));

        $em->expects($this->once())
            ->method('flush')
            ->will($this->returnValue(true));

        $atbSimulation->expects($this->any())
            ->method('getEM')
            ->will($this->returnValue($em));

        $atbSimulation->expects($this->any())
            ->method('findLastSimulationEntity')
            ->will($this->returnValue(null));

        return $atbSimulation;
    }

    protected function getMockReport($getValueCalls = 1)
    {
        $report = $this->getMock(
            'CreditJeeves\DataBundle\Entity\ReportPrequal',
            array(
                'getArfReport',
                'getArfParser',
            ),
            array(),
            '',
            false
        );

        $arfParser = $this->getMock(
            'CreditJeeves\CoreBundle\Arf\ArfParser',
            array('getValue'),
            array(),
            '',
            false
        );

        $report->expects($this->any())
            ->method('getArfParser')
            ->will($this->returnValue($arfParser));

        $arfReport = $this->getMock(
            'CreditJeeves\CoreBundle\Arf\ArfReport',
            array('getValue'),
            array(),
            '',
            false
        );

        $arfReport->expects($this->exactly($getValueCalls))
            ->method('getValue')
            ->will($this->returnValue(600));

        $report->expects($this->any())
            ->method('getArfReport')
            ->will($this->returnValue($arfReport));
        return $report;
    }

    /**
     * @test
     */
    public function cashSimulation()
    {
        $atbSimulation = $this->getMockAtbSimulation();
        $report = $this->getMockReport();

        /* @var $atbSimulation \CreditJeeves\ExperianBundle\AtbSimulation */
        $this->assertInstanceOf(
            'CreditJeeves\ExperianBundle\Model\Atb',
            $atbSimulation->simulate(AtbType::CASH, 1000, $report, 900)
        );
    }

    /**
     * @test
     *
     * @expectedException \CreditJeeves\ExperianBundle\Exception\Atb
     * @expectedExceptionMessage Wrong type '123'
     */
    public function cashSimulationWrongType()
    {
        $atbSimulation = $this->getMock(
            'CreditJeeves\ExperianBundle\AtbSimulation',
            array('findLastSimulationEntity'),
            array(),
            '',
            false
        );
        $report = $this->getMockReport();

        /* @var $atbSimulation \CreditJeeves\ExperianBundle\AtbSimulation */
        $this->assertInstanceOf(
            'CreditJeeves\ExperianBundle\Model\Atb',
            $atbSimulation->simulate('123', 1000, $report, 900)
        );
    }

    /**
     * @param $current
     * @param $iterations
     *
     * @return int
     */
    protected function getMidPoint($current, $iterations = 1, $max = AtbSimulation::SEARCH_CASH)
    {
        $iterations--;

        $result = $max - (int)(($max - $current) / 2);

        if ($iterations) {
            $result = $this->getMidPoint($result, $iterations, $max);
        }

        return $result;
    }

    public function cashBinarySearchData()
    {
        $middle = $this->getMidPoint(AtbSimulation::SEARCH_CASH / 4, 2, AtbSimulation::SEARCH_CASH / 2);

        return array(
            array(array(AtbSimulation::SEARCH_CASH => 500), 600, false),
            array(
                array(
                    AtbSimulation::SEARCH_CASH => 600,
                    (AtbSimulation::SEARCH_CASH / 2) => 500,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 2) => 599,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 2, 2) => 599,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 2, 3) => 599,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 2, 4) => 599,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 2, 5) => 599,
                ),
                600,
                array('score_best' => 600)
            ),
            array(
                array(
                    AtbSimulation::SEARCH_CASH => 700,
                    (AtbSimulation::SEARCH_CASH / 2) => 600,
                    (AtbSimulation::SEARCH_CASH / 4) => 599,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 4, 1, AtbSimulation::SEARCH_CASH / 2) => 599,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 4, 2, AtbSimulation::SEARCH_CASH / 2) => 599,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 4, 3, AtbSimulation::SEARCH_CASH / 2) => 599,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 4, 4, AtbSimulation::SEARCH_CASH / 2) => 599,
                ),
                600,
                array('score_best' => 600)
            ),
            array(
                array(
                    AtbSimulation::SEARCH_CASH => 700,
                    (AtbSimulation::SEARCH_CASH / 2) => 660,
                    (AtbSimulation::SEARCH_CASH / 4) => 500,
                    $this->getMidPoint(AtbSimulation::SEARCH_CASH / 4, 1, AtbSimulation::SEARCH_CASH / 2) => 550,
                    $middle => 600,
                    (int)($middle / 2) => 599,
                    $this->getMidPoint((int)($middle / 2), 1, $middle) => 599,
                    $this->getMidPoint((int)($middle / 2), 2, $middle) => 599,
                    $this->getMidPoint((int)($middle / 2), 3, $middle) => 599,
                    $this->getMidPoint((int)($middle / 2), 4, $middle) => 599,
                ),
                600,
                array('score_best' => 600)
            ),

        );
    }

    /**
     * @test
     * @dataProvider cashBinarySearchData
     */
    public function cashBinarySearch($return, $targetScore, $returnResult)
    {
        $atbSimulation = $this->getMock(
            'CreditJeeves\ExperianBundle\AtbSimulation',
            array(
                'findLastTransactionSignature',
                'getReport',
                'getAtb',
            ),
            array(),
            '',
            false
        );
        $report = $this->getMockReport(0);
        $atbSimulation->expects($this->any())
            ->method('getReport')
            ->will($this->returnValue($report));

        $atb = $this->getMock(
            'CreditJeeves\ExperianBundle\Atb',
            array(
                'bestUseOfCash'
            ),
            array(),
            '',
            false
        );

        $parser = $report->getArfParser();

        $map = array();

        foreach ($return as $provide => $ret) {
            $map[] = array(
                $parser,
                $provide,
                null,
                array('score_best' => $ret)
            );
        }

        $atb->expects($this->exactly(count($return)))
            ->method('bestUseOfCash')
            ->will($this->returnValueMap($map));

        $atbSimulation->expects($this->once())
            ->method('getAtb')
            ->will($this->returnValue($atb));

        $result = $this->callNoPublicMethod($atbSimulation, 'cashBinarySearch', array($targetScore));

        $this->assertEquals($returnResult, $result);

    }

    /**
     * @test
     */
    public function increaseScoreByX()
    {
        $this->markTestIncomplete('Finish');
    }
}
