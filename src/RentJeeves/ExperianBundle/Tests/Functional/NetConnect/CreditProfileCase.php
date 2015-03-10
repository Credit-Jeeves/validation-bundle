<?php
namespace RentJeeves\ExperianBundle\Tests\Functional\NetConnect;

use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\CoreBundle\Enum\ScoreModelType;
use CreditJeeves\ExperianBundle\Tests\Functional\NetConnect\CreditProfileCase as Base;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class CreditProfileCase extends Base
{
    protected $creditProfileClass = 'RentJeeves\ExperianBundle\NetConnect\CreditProfile';

    /**
     * @var string
     */
    const APP = 'AppRj';

    /**
     * @test
     */
    public function getResponseOnUserDataCorrect()
    {
        $this->markTestSkipped(
            'Experian Credit Reports no longer available. Need to replace with TU test.'
        );

        $arf = $this->getResponseOnUserData($this->user);
        $this->assertTrue(is_string($arf));
        $parser = new ArfParser($arf);
        $this->assertEquals(579, $parser->getReport()->getScore(ScoreModelType::VANTAGE3));
    }
}
