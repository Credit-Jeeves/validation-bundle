<?php
namespace CreditJeeves\TestBundle\NetConnect;

use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\ExperianBundle\NetConnect\PreciseID;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;

/**
 * DI\Service("experian.net_connect.precise_id") It is deffined in services.yml
 */
class PreciseIDTest extends PreciseID
{
    /**
     * @inheritdoc
     */
    public function getObjectOnUserData($user)
    {
        $this->createRequestOnUserData($user);
        $xml = file_get_contents(__DIR__ . '/../Resources/NetConnect/QuestionsResponse.xml');
        return $this->createResponse($xml);
    }

    /**
     * @inheritdoc
     */
    public function getResponseOnUserData(User $user)
    {
        $this->createRequestOnUserData($user);
        $response = file_get_contents(__DIR__ . '/../Resources/NetConnect/PreciseID-Response.xml');

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
            $response = file_get_contents(__DIR__ . '/../Resources/NetConnect/PreciseID-Questions-Response.xml');
        }

        return $this->retriveUserData($response);
    }
}
