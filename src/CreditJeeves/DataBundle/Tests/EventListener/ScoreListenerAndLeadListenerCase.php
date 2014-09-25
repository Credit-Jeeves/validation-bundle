<?php

namespace CreditJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\DataBundle\Entity\Dealer;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\DataBundle\Enum\LeadStatus;
use CreditJeeves\TestBundle\Functional\BaseTestCase;

class ScoreListenerAndLeadCase extends BaseTestCase
{
    /**
     * @test
     */
    public function score()
    {
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        /** @var Applicant $user */
        $user = $em->getRepository('DataBundle:Applicant')->findOneBy(
            array(
                'email' => 'mamazza@example.com',
            )
        );
        $em->refresh($user);
        $leads = $user->getUserLeads();
        /**
         * @var Lead $lead
         */
        foreach ($leads as $lead) {
            $this->assertTrue(
                ($lead->getStatus() === LeadStatus::ACTIVE),
                'Status not active '.$lead->getStatus().' '.$lead->getId()
            );
        }

        $this->assertTrue((!empty($user)), 'User does not exist');
        /**
         * @var Score $score
         */
        $score = $user->getCurrentScore();
        $this->assertTrue((empty($score)), 'score is empty');

        $score = new Score();
        $score->setUser($user);
        $score->setScore(850);

        $em->persist($score);
        $em->flush();
        $em->refresh($user);
        $leads = $user->getUserLeads();


        /**
         * @var Lead $lead
         */
        foreach ($leads as $lead) {
            $em->refresh($lead);
            $this->assertTrue(
                ($lead->getStatus() === LeadStatus::READY),
                'Status not active '.$lead->getStatus().' '.$lead->getId()
            );
            $this->assertTrue(
                ($lead->getFraction() >= 100),
                'Fraction != 100 it is == '.$lead->getFraction()
            );
        }
        $this->assertCount(1, $plugin->getPreSendMessages(), 'Wrong number of emails');
    }
}
