<?php

namespace RentJeeves\ComponentBundle\Tests\PidKiqProcessor;

use CreditJeeves\TestBundle\BaseTestCase;
use RentJeeves\ComponentBundle\PidKiqProcessor\PidKiqProcessorExperian;

class PidKiqProcessorExperianCase extends BaseTestCase
{
    /**
     * @test
     */
    public function retrieveQuestions()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        /** @var PidKiqProcessorExperian $experian */
        $experian = $this->getContainer()->get('experian.pidkiq_processor.factory')->getPidKiqProcessor($tenant);

        $questions = $experian->retrieveQuestions();
    }
}
