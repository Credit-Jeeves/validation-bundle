<?php
namespace CreditJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\DataBundle\Entity\Dealer;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Enum\LeadSource;
use CreditJeeves\DataBundle\Enum\LeadStatus;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\BaseTestCase;
use DateTime;

class LoggableListenerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function create()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var Applicant $applicant */
        $applicant = $em->getRepository('DataBundle:Applicant')->findOneByEmail('app12@example.com');
        /** @var Dealer $dealer */
        $dealer = $em->getRepository('DataBundle:Dealer')->findOneByEmail('support2@700credit.com');
        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->findOneByCode('DZC6K2PQC6');
        /** @var Lead $lead */
        $lead = new Lead();
        $lead->setStatus(LeadStatus::IDLE);
        $lead->setGroup($group);
        $lead->setDealer($dealer);
        $lead->setUser($applicant);
        $lead->setTargetScore(700);
        $lead->setTargetName('BMW X1');
        $lead->setSource(LeadSource::OFFICE);

        $em->persist($lead);
        $em->flush($lead);

        $contractHistory = $em->getRepository('DataBundle:LeadHistory')->findByObjectId($lead->getId());
        $this->assertNotNull($contractHistory);
        $this->assertCount(1, $contractHistory);

        // Update
        $lead->setTargetScore(690);
        $em->persist($lead);
        $em->flush($lead);
        $em->clear();
        static::$kernel = null;

        $contractHistory = $em->getRepository('DataBundle:LeadHistory')->findByObjectId($lead->getId());
        $this->assertNotNull($contractHistory);
        $this->assertCount(2, $contractHistory);

    }
}
