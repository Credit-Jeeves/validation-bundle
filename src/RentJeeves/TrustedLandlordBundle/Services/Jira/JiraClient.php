<?php

namespace RentJeeves\TrustedLandlordBundle\Services\Jira;

use JiraClient\JiraClient as Client;
use JiraClient\Resource\Field;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\TrustedLandlord;

class JiraClient
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $projectKey;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Logger $logger
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $projectKey
     */
    public function __construct(LoggerInterface $logger, $host, $user, $password, $projectKey)
    {
        $this->client = new Client($host, $user, $password);
        $this->projectKey = $projectKey;
        $this->logger = $logger;
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @return \JiraClient\Resource\Issue
     */
    public function createTrustedLandlordIssue(TrustedLandlord $trustedLandlord)
    {
        $this->logger->debug('Call createTrustedLandlordIssue for TrustedLandlord#' . $trustedLandlord->getId());
        $newIssue = $this->client->issue()
            ->create($this->projectKey, 'Task')
            ->field(Field::SUMMARY, $trustedLandlord->getFullName() . ' Verification')
            ->customField('10601', $trustedLandlord->getType())
            ->customField('10602', $trustedLandlord->getCompanyName())
            ->customField('10603', $trustedLandlord->getFirstName())
            ->customField('10604', $trustedLandlord->getLastName())
            ->customField('10605', $trustedLandlord->getPhone())
            //->customField('10621', '') // email, What I should use?
            ->customField('10609', $trustedLandlord->getCheckMailingAddress()->getAddressee())
            ->customField('10610', $trustedLandlord->getCheckMailingAddress()->getAddress1())
            ->customField('10611', $trustedLandlord->getCheckMailingAddress()->getAddress2())
            ->customField('10612', $trustedLandlord->getCheckMailingAddress()->getCity())
            ->customField('10613', $trustedLandlord->getCheckMailingAddress()->getState())
            ->customField('10614', $trustedLandlord->getCheckMailingAddress()->getZip())
            ->execute();

        return $newIssue;
    }

    /**
     * @param $key
     * @return \JiraClient\Resource\Issue
     */
    public function getIssue($key)
    {
        return $this->client->issue()->get($key);
    }
}
