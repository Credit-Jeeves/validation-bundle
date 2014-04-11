<?php
namespace RentJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\CoreBundle\Enum\ScoreModelType;
use CreditJeeves\ExperianBundle\Tests\Functional\NetConnectCase as BaseTestCase;

/**
 * NetConnect test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class NetConnectCase extends BaseTestCase
{
    /**
     * @var string
     */
    const APP = 'AppRj';

    /**
     * @test
     */
    public function getResponseOnUserDataCorrect()
    {
        $arf = $this->getResponseOnUserData($this->user);
        $this->assertTrue(is_string($arf));
        $parser = new ArfParser($arf);
        $this->assertEquals(234, $parser->getReport()->getScore(ScoreModelType::VANTAGE3));

        $this->getContainer()->get('experian.net_connect');
    }

}
