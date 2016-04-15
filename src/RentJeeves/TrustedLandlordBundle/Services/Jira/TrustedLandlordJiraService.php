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
use RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO;
use RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordService;

/**
 * trusted_landlord.jira.service
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
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var TrustedLandlordService
     */
    protected $trustedLandlordService;

    /**
     * @param TrustedLandlordService       $trustedLandlordService
     * @param JiraClient                   $client
     * @param EntityManager                $em
     * @param LoggerInterface              $logger
     * @param Serializer                   $serializer
     */
    public function __construct(
        TrustedLandlordService $trustedLandlordService,
        JiraClient $client,
        EntityManager $em,
        LoggerInterface $logger,
        Serializer $serializer
    ) {
        $this->client = $client;
        $this->em = $em;
        $this->trustedLandlordService = $trustedLandlordService;
        $this->logger = $logger;
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
                sprintf(
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
            $this->logger->alert(
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

        $this->trustedLandlordService->update(
            $trustedLandlord,
            strtolower($trustedLandlordIssue->getTransition()->getToStatus()),
            $this->mapToTrustedLandlordDTOValues($trustedLandlordIssue->getIssue()->getFields())
        );

        return true;
    }

    /**
     * @param Fields $fields
     * @return TrustedLandlordDTO
     */
    public function mapToTrustedLandlordDTOValues(Fields $fields)
    {
        $trustedLandlordDTO = new TrustedLandlordDTO();
        $trustedLandlordDTO->setFirstName($fields->getFirstName());
        $trustedLandlordDTO->setLastName($fields->getLastName());
        $trustedLandlordDTO->setCompanyName($fields->getCompanyName());
        $trustedLandlordDTO->setType(strtolower($fields->getType()->getValue()));
        $trustedLandlordDTO->setPhone($fields->getPhone());
        $trustedLandlordDTO->setAddressee($fields->getAddressee());
        $trustedLandlordDTO->setAddress1($fields->getAddress1());
        $trustedLandlordDTO->setAddress2($fields->getAddress2());
        $trustedLandlordDTO->setCity($fields->getCity());
        $trustedLandlordDTO->setState($fields->getState());
        $trustedLandlordDTO->setZip($fields->getZip());

        return $trustedLandlordDTO;
    }
}
