<?php
namespace CreditJeeves\ExperianBundle\Tests;

use CreditJeeves\CoreBundle\Tests\BaseTestCase;
use CreditJeeves\ExperianBundle\AtbSimulation;
use CreditJeeves\ExperianBundle\Simulation;
use CreditJeeves\DataBundle\Enum\AtbType;
use CreditJeeves\ExperianBundle\Model\Atb as Model;

class AtbSimulationCase extends BaseTestCase
{
    /**
     * @test
     */
    public function itMustBeConstructed()
    {
        $atb = $this->getMock('CreditJeeves\ExperianBundle\Atb', array(), array(), '', false);
        $converter = $this->getMock('CreditJeeves\ExperianBundle\Converter\Atb', array(), array(), '', false);
        $em = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
        $entityRepo = $this->getMock('CreditJeeves\DataBundle\Entity\AtbRepository', array(), array(), '', false);
        $atbSimulation = new AtbSimulation($atb, $converter, $em, $entityRepo);
    }

    /**
     * @test
     */
    public function cashSimulation()
    {

        $atbSimulation = $this->getMock(
            'CreditJeeves\ExperianBundle\AtbSimulation',
            array(
                'getAtb',
                'getConverter',
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

        $report = $this->getMock(
            'CreditJeeves\DataBundle\Entity\ReportPrequal',
            array(
                'getArfParser'
            ),
            array(),
            '',
            false
        );

        $arfParser = $this->getMock(
            'CreditJeeves\CoreBundle\Arf\ArfParser',
            array(
                'getArfParser'
            ),
            array(),
            '',
            false
        );

        $report->expects($this->once())
            ->method('getArfParser')
            ->will($this->returnValue($arfParser));


        /* @var $atbSimulation \CreditJeeves\ExperianBundle\AtbSimulation */
        $this->assertInstanceOf(
            'CreditJeeves\ExperianBundle\Model\Atb',
            $atbSimulation->simulate(AtbType::CASH, 1000, $report)
        );
    }
}
