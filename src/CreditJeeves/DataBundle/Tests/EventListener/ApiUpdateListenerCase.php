<?php

namespace CreditJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Dealer;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\TestBundle\Functional\BaseTestCase;

class ApiUpdateListenerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function postUpdate()
    {
        $this->load(true);
        $container = $this->getContainer();
        $em = $container->get('doctrine');
        $em->getManager()->clear();

        $dealerCode = $container->getParameter('api.admin_dealer_code');
        /** @var $dealer Dealer */
        $dealer = $em->getRepository('DataBundle:Dealer')->findOneBy(
            array(
                'invite_code' => $dealerCode,
            )
        );
        $this->assertTrue(($dealer instanceof Dealer), 'Dealer does not exist');
        $leads = $dealer->getDealerLeads();
        /** @var $lead Lead */
        $lead = $leads->first();
        $lead->setFraction(60);
        $applicantId = $lead->getCjApplicantId();
        $em->getManager()->persist($lead);
        $em->getManager()->flush();

        $apiUpdate = $em->getRepository('DataBundle:ApiUpdate')->findOneBy(
            array(
                'user' => $applicantId,
            )
        );

        $this->assertTrue((!empty($apiUpdate)), 'ApiUpdate do not saving');
        $lead->setFraction(50);
        $em->getManager()->persist($lead);
        $em->getManager()->flush();

        $apiUpdate = $em->getRepository('DataBundle:ApiUpdate')->findOneBy(
            array(
                'user' => $applicantId,
            )
        );

        $this->assertTrue((!empty($apiUpdate)), 'ApiUpdate do not saving');
        $em->getManager()->remove($apiUpdate);
        $em->getManager()->flush();

        $applicant = $lead->getUser();

        $applicant->setPhone('1233333339');
        $em->getManager()->persist($applicant);
        $em->getManager()->flush();

        $apiUpdate = $em->getRepository('DataBundle:ApiUpdate')->findOneBy(
            array(
                'user' => $applicant->getId(),
            )
        );

        $this->assertTrue((!empty($apiUpdate)), 'ApiUpdate do not saving');

        $applicant = $em->getRepository('DataBundle:Applicant')->findOneBy(
            array(
                'email' => 'alexey.karpik+app1334753295955955@gmail.com',
            )
        );

        $applicant->setPhone('1233333339');
        $em->getManager()->persist($applicant);
        $em->getManager()->flush();

        $apiUpdate = $em->getRepository('DataBundle:ApiUpdate')->findOneBy(
            array(
                'user' => $applicant->getId(),
            )
        );

        $this->assertTrue((empty($apiUpdate)), 'ApiUpdate do saving, but not need saving');
    }
}
