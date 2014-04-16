<?php
namespace RentJeeves\TestBundle\Tests\Experian;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\TestBundle\BaseTestCase;
use RuntimeException;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class NetConnectCase extends BaseTestCase
{
    /**
     * @test
     *
     * @expectedException RuntimeException
     */
    public function getResponseOnUserDataException()
    {
        $this->getContainer()->get('experian.net_connect')->getResponseOnUserData(new Applicant());
    }
}
