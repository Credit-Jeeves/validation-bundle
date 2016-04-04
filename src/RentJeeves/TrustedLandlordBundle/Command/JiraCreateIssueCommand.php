<?php

namespace RentJeeves\TrustedLandlordBundle\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\TrustedLandlordBundle\Services\Jira\TrustedLandlordJiraService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JiraCreateIssueCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdioc}
     */
    protected function configure()
    {
        $this
            ->setName('api:jira:create-issue')
            ->addOption(
                'jms-job-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Job ID'
            )
            ->addOption(
                'trusted-landlord-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Accounting batch id exist for some accounting system'
            )
            ->setDescription(
                'Create new issue on jira.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $trustedLandlordJira = $em->getRepository('RjDataBundle:TrustedLandlord')->find($input->getOption('trusted-landlord-id'));
        if (empty($trustedLandlordJira)) {
            throw new \LogicException(
                sprintf('Option trusted-landlord-id#%s for command is wrong', $input->getOption('trusted-landlord-id'))
            );
        }
        /** @var TrustedLandlordJiraService $trustedLandlordJiraService */
        $trustedLandlordJiraService = $this->getContainer()->get('trusted.landlord.jira.service');
        $jiraMapping = $trustedLandlordJiraService->addToQueue($trustedLandlordJira);
        if (empty($jiraMapping)) {
            return 1;
        }

        return 0;
    }
}
