<?php
namespace CreditJeeves\TestBundle\NetConnect\Traits;

use CreditJeeves\DataBundle\Entity\User;

trait PreciseIDTest
{
    /**
     * @inheritdoc
     */
    public function getObjectOnUserData($user)
    {
        $this->createRequestOnUserData($user);
        $xml = file_get_contents(__DIR__ . '/../../Resources/NetConnect/QuestionsResponse.xml');
        return $this->createResponse($xml);
    }

    /**
     * @inheritdoc
     */
    public function getResponseOnUserData(User $user)
    {
        $this->createRequestOnUserData($user);
        $response = file_get_contents(__DIR__ . '/../../Resources/NetConnect/PreciseID-Response.xml');

        return $this->retriveUserData($this->createResponse($response));
    }

    /**
     * @inheritdoc
     */
    public function getResult($sessionId, $answers)
    {
        if (count(array_diff_assoc(array_values($answers), array(1, 2, 3, 4)))) {
            $response = file_get_contents(
                __DIR__ . '/../Resources/NetConnect/PreciseID-Questions-Response-WrongAnswers.xml'
            );
        } else {
            $response = file_get_contents(__DIR__ . '/../../Resources/NetConnect/PreciseID-Questions-Response.xml');
        }

        return $this->retriveUserData($response);
    }
}
