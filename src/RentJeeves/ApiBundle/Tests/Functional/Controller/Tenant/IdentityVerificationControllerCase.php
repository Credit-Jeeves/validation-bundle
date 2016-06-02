<?php

namespace RentJeeves\ApiBundle\Tests\Functional\Controller\Tenant;

use CreditJeeves\DataBundle\Entity\Pidkiq;
use CreditJeeves\DataBundle\Enum\PidkiqStatus;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use RentJeeves\ApiBundle\Tests\BaseApiTestCase;
use RentJeeves\TestBundle\PidKiqProcessor\Experian\ExperianPidKiqApiClientTest;
use Symfony\Component\Yaml\Yaml;

class IdentityVerificationControllerCase extends BaseApiTestCase
{
    const REQUEST_URL = 'identity_verification';

    /**
     * @var array
     */
    protected $questions;

    /**
     * @return Pidkiq
     */
    protected function preparedModel()
    {
        /** @var Pidkiq $pidkiq */
        $pidkiq = new Pidkiq();

        $pidkiq->setUser($this->getUser());
        $pidkiq->setStatus(PidkiqStatus::INPROGRESS);
        $pidkiq->setSessionId(ExperianPidKiqApiClientTest::TEST_SESSION_ID);
        $pidkiq->setQuestions(
            $this->getQuestions()
        );

        $this->getEm()->persist($pidkiq);
        $this->getEm()->flush($pidkiq);

        return $pidkiq;
    }

    /**
     * @return array
     */
    protected function getQuestions()
    {
        if (!$this->questions) {
            $this->questions = Yaml::parse(
                $this->getKernel()->locateResource('@RjComponentBundle/Tests/Fixtures/Pidkiq/questions.yml')
            );
        }

        return $this->questions;
    }

    /**
     * @test
     */
    public function getIdentityVerificationAction()
    {
        $this->prepareClient(true);

        $pidkiq = $this->preparedModel();

        $encodedId = $this->getIdEncoder()->encode($pidkiq->getId());

        $response = $this->getRequest($encodedId);

        $this->assertResponse($response);

        $answer = $this->parseContent($response->getContent());

        $this->assertNotEmpty($answer['expires']);

        unset($answer['expires']);

        $expected = [
            'id' => $encodedId,
            'url' => $this->prepareUrl($encodedId, false, self::REQUEST_URL, true),
            'status' => PidkiqStatus::INPROGRESS,
            'message' => null,
            'questions' => $this->getFormattedQuestions($this->getQuestions()),
        ];

        $this->assertEquals($expected, $answer);

        return $encodedId;
    }

    /**
     * @param array $questions
     * @return array
     */
    protected function getFormattedQuestions(array $questions)
    {
        $keys = array_keys($questions);

        return [
            [
                'id' => 1,
                'question' => $keys[0],
                'choices' => [
                    [
                        'id' => 1,
                        'choice' => current($questions)[1]
                    ],
                    [
                        'id' => 2,
                        'choice' => current($questions)[2]
                    ],
                    [
                        'id' => 3,
                        'choice' => current($questions)[3]
                    ],
                    [
                        'id' => 4,
                        'choice' => current($questions)[4]
                    ],
                    [
                        'id' => 5,
                        'choice' => current($questions)[5]
                    ],
                ]
            ],
            [
                'id' => 2,
                'question' => $keys[1],
                'choices' => [
                    [
                        'id' => 1,
                        'choice' => next($questions)[1]
                    ],
                    [
                        'id' => 2,
                        'choice' => current($questions)[2]
                    ],
                    [
                        'id' => 3,
                        'choice' => current($questions)[3]
                    ],
                    [
                        'id' => 4,
                        'choice' => current($questions)[4]
                    ],
                    [
                        'id' => 5,
                        'choice' => current($questions)[5]
                    ],
                ]
            ],
            [
                'id' => 3,
                'question' => $keys[2],
                'choices' => [
                    [
                        'id' => 1,
                        'choice' => next($questions)[1]
                    ],
                    [
                        'id' => 2,
                        'choice' => current($questions)[2]
                    ],
                    [
                        'id' => 3,
                        'choice' => current($questions)[3]
                    ],
                    [
                        'id' => 4,
                        'choice' => current($questions)[4]
                    ],
                    [
                        'id' => 5,
                        'choice' => current($questions)[5]
                    ],
                ]
            ],
            [
                'id' => 4,
                'question' => $keys[3],
                'choices' => [
                    [
                        'id' => 1,
                        'choice' => next($questions)[1]
                    ],
                    [
                        'id' => 2,
                        'choice' => current($questions)[2]
                    ],
                    [
                        'id' => 3,
                        'choice' => current($questions)[3]
                    ],
                    [
                        'id' => 4,
                        'choice' => current($questions)[4]
                    ],
                    [
                        'id' => 5,
                        'choice' => current($questions)[5]
                    ],
                ]
            ],
        ];
    }

    /**
     * @param int $encodedId
     * @test
     * @depends getIdentityVerificationAction
     */
    public function sendIdentityVerificationAction($encodedId)
    {
        $answers = [
            'answers' => [
                [2 => 3],
                [1 => 2],
                [3 => 3],
                [4 => 5],
            ]
        ];

        $response = $this->putRequest($encodedId, $answers);

        $this->assertResponse($response);

        $answer = $this->parseContent($response->getContent());

        $this->assertEquals(PidkiqStatus::SUCCESS, $answer['status']);

        $this->getEm()->refresh($this->getUser());

        $this->assertEquals(UserIsVerified::PASSED, $this->getUser()->getIsVerified());
    }

    /**
     * @test
     */
    public function shouldReturn200ResponseWithStatusWhenAnswersAreIncorrect()
    {
        $this->prepareClient(true);

        $pidkiq = $this->preparedModel();
        $encodedId = $this->getIdEncoder()->encode($pidkiq->getId());
        $response = $this->getRequest($encodedId);
        $this->assertResponse($response);

        $answers = [
            'answers' => [
                [2 => 1], // incorrect answer
                [1 => 2],
                [3 => 3],
                [4 => 5],
            ]
        ];

        $response = $this->putRequest($encodedId, $answers);
        $this->assertResponse($response);

        $answer = $this->parseContent($response->getContent());
        $this->assertArrayHasKey('status', $answer);
        $this->assertEquals(PidkiqStatus::FAILURE, $answer['status']);
    }
}
