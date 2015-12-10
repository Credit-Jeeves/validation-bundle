<?php

namespace RentJeeves\TestBundle\Traits;

use RentJeeves\DataBundle\Entity\Job;

/**
 * Job Assertion Trait was created for assert created jobs
 *
 * @method assertEquals($expected, $actual, $message = '')
 */
trait JobAssertionTrait
{
    /**
     * @param Job $job
     * @param string $commandName
     * @param array $args
     */
    protected function assertJob(Job $job, $commandName, array $args = [])
    {
        $this->assertEquals(
            $commandName,
            $job->getCommand(),
            sprintf(
                'Command name on job is incorrect, should be "%s" instead "%s"',
                $commandName,
                $job->getCommand()
            )
        );

        $this->assertEquals(
            $args,
            $job->getArgs(),
            sprintf(
                "Arguments on job are incorrect should be:\n%s\ninstead\n%s",
                print_r($args, true),
                print_r($job->getArgs(), true)
            )
        );
    }
}
