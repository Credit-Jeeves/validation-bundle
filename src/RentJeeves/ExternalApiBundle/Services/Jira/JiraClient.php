<?php

namespace RentJeeves\ExternalApiBundle\Services\Jira;

use JiraClient\JiraClient as Client;
use JiraClient\Resource\Field;
use JiraClient\Resource\IssueType;
use Psr\Log\LoggerInterface;

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
    }

    /**
     * @param string $issueName
     * @param string $description
     * @return \JiraClient\Resource\Issue
     */
    public function createIssue($issueName, $description)
    {
        $newIssue = $this->client->issue()
            ->create($this->projectKey, 'Task')
            ->field(Field::SUMMARY, $issueName)
            ->field(Field::DESCRIPTION, $description)
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

