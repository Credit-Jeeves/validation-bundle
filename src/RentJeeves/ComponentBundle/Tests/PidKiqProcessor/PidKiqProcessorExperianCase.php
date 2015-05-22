<?php

namespace RentJeeves\ComponentBundle\Tests\PidKiqProcessor;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\PidkiqStatus;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\ORM\EntityManager;
use RentJeeves\ComponentBundle\PidKiqProcessor\Experian\ExperianPidKiqApiClient;
use RentJeeves\ComponentBundle\PidKiqProcessor\PidKiqExperianProcessor;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class PidKiqProcessorExperianCase extends BaseTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ExperianPidKiqApiClient
     */
    protected $experianMockApi;

    /**
     * @var PidKiqExperianProcessor
     */
    protected $pidkiqProcessor;

    /**
     * @var FileLocator
     */
    protected $fixtureLoader;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var User
     */
    protected $user;

    protected function load($reload = false)
    {
        parent::load($reload);
        $this->setUp(); // this need because we have some problems with doctrine after reload fixtures
    }

    public function setUp()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $this->fixtureLoader = new FileLocator(
            [__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'Pidkiq']
        );

        $this->experianMockApi = $this->getMock(
            'RentJeeves\ComponentBundle\PidKiqProcessor\Experian\ExperianPidKiqApiClient',
            ['__send'],
            [
                $this->getContainer()->get('logger'),
                $this->getContainer()->get('kernel'),
                $this->em
            ]
        );

        $this->experianMockApi->setConfig($this->getContainer()->getParameter('pidkiq.experian_api.config'));

        $this->user = $this
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');

        $this->pidkiqProcessor = new PidKiqExperianProcessor(
            $this->getContainer()->get('security.context'),
            $this->em,
            $this->getContainer()->get('pidkiq.message_generator')
        );

        $this->pidkiqProcessor->setUser($this->user);

        $this->pidkiqProcessor->setPidKiqApiClient($this->experianMockApi);
    }

    /**
     * @test
     */
    public function retrieveQuestionsSuccess()
    {
        $this->load(true);

        $this->experianMockApi
            ->expects($this->once())
            ->method('__send')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            $this->fixtureLoader->locate('ExperianRetrieveQuestions-Request.xml'),
                            $xml
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixtureLoader->locate('ExperianRetrieveQuestions-Response.xml'))
                )
            );

        $questions = $this->pidkiqProcessor->retrieveQuestions();

        $this->assertStringEqualsFile(
            $this->fixtureLoader->locate('questions.yml'),
            Yaml::dump($questions)
        );
    }

    /**
     * @test
     * @depends retrieveQuestionsSuccess
     */
    public function retrieveQuestionsFromDb()
    {
        $this->experianMockApi
            ->expects($this->never())
            ->method('__send');

        $questions = $this->pidkiqProcessor->retrieveQuestions();

        $this->assertStringEqualsFile(
            $this->fixtureLoader->locate('questions.yml'),
            Yaml::dump($questions)
        );
    }

    /**
     * @test
     * @depends retrieveQuestionsSuccess
     */
    public function processAnswersFailure()
    {
        $this->experianMockApi
            ->expects($this->once())
            ->method('__send')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            $this->fixtureLoader->locate('ExperianProcessAnswers-Request.xml'),
                            $xml
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixtureLoader->locate('ExperianProcessAnswers-Response-Wrong.xml'))
                )
            );

        $this->pidkiqProcessor->processAnswers(Yaml::parse($this->fixtureLoader->locate('answers.yml')));

        $this->em->refresh($this->user);

        $this->assertEquals(PidkiqStatus::FAILURE, $this->pidkiqProcessor->getPidkiqModel()->getStatus());

        $this->assertEquals(UserIsVerified::FAILED, $this->user->getIsVerified());
    }

    /**
     * @test
     * @depends retrieveQuestionsSuccess
     */
    public function processAnswersSuccess()
    {
        $this->experianMockApi
            ->expects($this->once())
            ->method('__send')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            $this->fixtureLoader->locate('ExperianProcessAnswers-Request.xml'),
                            $xml
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixtureLoader->locate('ExperianProcessAnswers-Response.xml'))
                )
            );

        $this->pidkiqProcessor->processAnswers(Yaml::parse($this->fixtureLoader->locate('answers.yml')));

        $this->em->refresh($this->user);

        $this->assertEquals(PidkiqStatus::SUCCESS, $this->pidkiqProcessor->getPidkiqModel()->getStatus());

        $this->assertEquals(UserIsVerified::PASSED, $this->user->getIsVerified());
    }

    /**
     * @test
     */
    public function retrieveQuestionsCannotFormulate()
    {
        $this->load(true);

        $this->experianMockApi
            ->expects($this->once())
            ->method('__send')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            $this->fixtureLoader->locate('ExperianRetrieveQuestions-Request.xml'),
                            $xml
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    file_get_contents(
                        $this->fixtureLoader->locate('ExperianRetrieveQuestions-Response-CannotFormulateQuestions.xml')
                    )
                )
            );

        $questions = $this->pidkiqProcessor->retrieveQuestions();

        $this->assertCount(0, $questions);

        $this->assertFalse($this->pidkiqProcessor->getIsSuccessfull());

        $this->assertEquals(PidkiqStatus::UNABLE, $this->pidkiqProcessor->getPidkiqModel()->getStatus());
    }

    /**
     * @test
     */
    public function retrieveQuestionsDeceased()
    {
        $this->load(true);

        $this->experianMockApi
            ->expects($this->once())
            ->method('__send')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            $this->fixtureLoader->locate('ExperianRetrieveQuestions-Request.xml'),
                            $xml
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    file_get_contents(
                        $this->fixtureLoader->locate('ExperianRetrieveQuestions-Response-Deceased.xml')
                    )
                )
            );

        $questions = $this->pidkiqProcessor->retrieveQuestions();
        $this->em->refresh($this->user);

        $this->assertCount(0, $questions);

        $this->assertFalse($this->pidkiqProcessor->getIsSuccessfull());

        $this->assertEquals(PidkiqStatus::LOCKED, $this->pidkiqProcessor->getPidkiqModel()->getStatus());

        $this->assertEquals(UserIsVerified::LOCKED, $this->user->getIsVerified());
    }

    /**
     * @test
     */
    public function retrieveQuestionsNoQuestions()
    {
        $this->load(true);

        $this->experianMockApi
            ->expects($this->once())
            ->method('__send')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            $this->fixtureLoader->locate('ExperianRetrieveQuestions-Request.xml'),
                            $xml
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    file_get_contents(
                        $this->fixtureLoader->locate(
                            'ExperianRetrieveQuestions-Response-NoQuestionsReturnedDueToExcessiveUse.xml'
                        )
                    )
                )
            );

        $questions = $this->pidkiqProcessor->retrieveQuestions();

        $this->assertCount(0, $questions);

        $this->assertFalse($this->pidkiqProcessor->getIsSuccessfull());

        $this->assertEquals(PidkiqStatus::BACKOFF, $this->pidkiqProcessor->getPidkiqModel()->getStatus());
    }
}
