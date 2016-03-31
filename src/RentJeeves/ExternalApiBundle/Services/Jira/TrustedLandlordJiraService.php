<?php

namespace RentJeeves\ExternalApiBundle\Services\Jira;


use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping;
use JiraClient\Exception\JiraException;
use RentJeeves\LandlordBundle\Services\TrustedLandlordStatusManager;

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
     * @param TrustedLandlordStatusManager $statusManager
     * @param JiraClient                   $client
     * @param EntityManager                $em
     * @param LoggerInterface              $logger
     */
    public function __construct(
        TrustedLandlordStatusManager $statusManager,
        JiraClient $client,
        EntityManager $em,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->em = $em;
        $this->logger = $logger;
        $this->statusManager = $statusManager;
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
     * @return bool
     */
    public function handleWebhookEvent(array $data)
    {
        $jiraKey = $this->getJiraKey($data);
        $jiraStatus = strtolower($this->getJiraStatus($data));

        if (empty($jiraKey) || empty($jiraStatus)) {
            $this->logger->debug(sprintf('JiraKey#%s or JiraStatus#%s missed.', $jiraKey, $jiraStatus));

            return false;
        }

        $trustedLandlordJiraMapping = $this->em->getRepository('RjDataBundle:TrustedLandlordJiraMapping')->findOneBy(
            ['jiraKey' => $jiraKey]
        );

        if (empty($trustedLandlordJiraMapping)) {
            $this->logger->debug('trustedLandlordJiraMapping  missed in database, jiraKey#' . $jiraKey);

            return false;
        }


        return $this->statusManager->updateStatus($trustedLandlordJiraMapping->getTrustedLandlord(), $jiraStatus);
    }

    /**
     * @param array $data
     * @return bool|string
     */
    protected function getJiraKey(array $data)
    {
        if (!isset($data['issue'])) {
            $this->logger->debug('Data structure is invalid, we don\'t have "issue" key in array');

            return false;
        }

        $issue = $data['issue'];

        if (!isset($issue['key'])) {
            $this->logger->debug('Data structure is invalid, we don\'t have "key" key in array');

            return false;
        }

        return $issue['key'];
    }

    /**
     * @param array $data
     * @return bool|string
     */
    protected function getJiraStatus(array $data)
    {
        if (!isset($data['transition'])) {
            $this->logger->debug('Data structure is invalid, we don\'t have "transition" key in array');

            return false;
        }

        $transition = $data['transition'];

        if (!isset($transition['to_status'])) {
            $this->logger->debug('Data structure is invalid, we don\'t have "to_status" key in array');

            return false;
        }

        return $transition['to_status'];
    }
}

