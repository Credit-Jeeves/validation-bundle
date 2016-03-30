<?php

namespace RentJeeves\ExternalApiBundle\Services\Jira;


use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping;
use JiraClient\Exception\JiraException;
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
     * @param JiraClient $client
     * @param EntityManager $em
     * @param LoggerInterface $logger
     */
    public function __construct(JiraClient $client, EntityManager $em, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param TrustedLandlord $trustedLandlordRecord
     *
     * @return TrustedLandlordJiraMapping|null
     */
    public function addToQueue(TrustedLandlord $trustedLandlordRecord)
    {
        $title = $trustedLandlordRecord->getFullName() . " Verification";
        $description = $trustedLandlordRecord->getCheckMailingAddress()->getFullAddress();
        try {
            $issue = $this->client->createIssue($title, $description);
        } catch (JiraException $e) {
            $this->logger->alert(
                $message = sprintf(
                    'We got exception when tried to create an issue for trustedLandlordRecord#%s',
                    $trustedLandlordRecord->getId()
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
     * @return boolean
     */
    public function handleWebhookEvent(array $data)
    {
        if (!isset($data['issue'])) {
            $this->logger->debug('Data structure is invalid, we don\'t have issue key in array');

            return false;
        }

        $issue = $data['issue'];

        if (!isset($issue['key'])) {
            $this->logger->debug('Data structure is invalid, we don\'t have "key" key in array');

            return false;
        }

        $jiraKey = $issue['key'];
    }
}

