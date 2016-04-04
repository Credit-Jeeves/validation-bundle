<?php

namespace RentJeeves\TrustedLandlordBundle\Services\Jira;


use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping;
use JiraClient\Exception\JiraException;
use RentJeeves\TrustedLandlordBundle\Model\Jira\Fields;
use RentJeeves\TrustedLandlordBundle\Model\Jira\TrustedLandlordIssue;
use RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordStatusManager;

/**
 * trusted.landlord.jira.service
 */
class TrustedLandlordJiraService
{
    /**
     * @var JiraClient
     */
    protected $client;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TrustedLandlordStatusManager
     */
    protected $statusManager;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param TrustedLandlordStatusManager $statusManager
     * @param JiraClient                   $client
     * @param EntityManager                $em
     * @param LoggerInterface              $logger
     * @param Serializer                   $serializer
     */
    public function __construct(
        TrustedLandlordStatusManager $statusManager,
        JiraClient $client,
        EntityManager $em,
        LoggerInterface $logger,
        Serializer $serializer
    ) {
        $this->client = $client;
        $this->em = $em;
        $this->logger = $logger;
        $this->statusManager = $statusManager;
        $this->serializer = $serializer;
    }

    /**
     * @param TrustedLandlord $trustedLandlordRecord
     *
     * @return TrustedLandlordJiraMapping|null
     */
    public function addToQueue(TrustedLandlord $trustedLandlordRecord)
    {
        try {
            $issue = $this->client->createTrustedLandlordIssue($trustedLandlordRecord);
        } catch (JiraException $e) {
            $this->logger->alert(
                $message = sprintf(
                    'We got exception when tried to create an issue for trustedLandlordRecord#%s. Exception: %s',
                    $trustedLandlordRecord->getId(),
                    $e->getMessage()
                )
            );

            return null;
        }

        $jiraMapping = new TrustedLandlordJiraMapping();
        $jiraMapping->setJiraKey($issue->getKey());
        $jiraMapping->setTrustedLandlord($trustedLandlordRecord);
        $this->em->persist($jiraMapping);
        $this->em->flush();

        $this->logger->debug(
            sprintf(
                'Created issue#%s for trustedLandlordRecord#%s',
                $jiraMapping->getJiraKey(),
                $trustedLandlordRecord->getId()
            )
        );

        return $jiraMapping;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function handleWebhookEvent(array $data)
    {
        /** @var TrustedLandlordIssue $trustedLandlordIssue */
        $trustedLandlordIssue = $this->serializer->deserialize(
            $data,
            'RentJeeves\TrustedLandlordBundle\Model\Jira\TrustedLandlordIssue',
            'array'
        );

        $trustedLandlordIssue->getTransition();
        $trustedLandlordIssue->getIssue();

        if (empty($trustedLandlordIssue->getTransition()) || empty($trustedLandlordIssue->getIssue())) {
            $this->logger->debug(
                sprintf('Required data missed. Wrong deserialize, source:%s', print_r($data, true))
            );

            return false;
        }

        $trustedLandlordJiraMapping = $this->em->getRepository('RjDataBundle:TrustedLandlordJiraMapping')->findOneBy(
            ['jiraKey' => $trustedLandlordIssue->getIssue()->getKey()]
        );

        if (empty($trustedLandlordJiraMapping)) {
            $this->logger->debug(
                'trustedLandlordJiraMapping  missed in database, jiraKey#' . $trustedLandlordIssue->getIssue()->getKey()
            );

            return false;
        }

        $trustedLandlord = $trustedLandlordJiraMapping->getTrustedLandlord();
        $this->mapToTrustedLandlordNewValues($trustedLandlord, $trustedLandlordIssue->getIssue()->getFields());

        return $this->statusManager->updateStatus(
            $trustedLandlord,
            strtolower($trustedLandlordIssue->getTransition()->getToStatus())
        );
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @param Fields $fields
     */
    public function mapToTrustedLandlordNewValues(TrustedLandlord $trustedLandlord, Fields $fields)
    {
        $trustedLandlord->setFirstName($fields->getFirstName());
        $trustedLandlord->setLastName($fields->getLastName());
        $trustedLandlord->setCompanyName($fields->getCompanyName());
        $trustedLandlord->setType(strtolower($fields->getType()->getValue()));
        $trustedLandlord->setPhone($fields->getPhone());

        $checkMailingAddress = $trustedLandlord->getCheckMailingAddress();
        $checkMailingAddress->setAddressee($fields->getAddressee());
        $checkMailingAddress->setAddress1($fields->getAddress1());
        $checkMailingAddress->setAddress2($fields->getAddress2());
        $checkMailingAddress->setCity($fields->getCity());
        $checkMailingAddress->setState($fields->getState());
        $checkMailingAddress->setZip($checkMailingAddress->getZip());
    }
}

