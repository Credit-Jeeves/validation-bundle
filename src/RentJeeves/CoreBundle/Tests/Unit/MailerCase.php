<?php
namespace RentJeeves\CoreBundle\Mailer;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class MailerCase extends BaseTestCase
{

    /**
     * @test
     */
    public function test1()
    {
        $tenant = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);

        $a = $this->getMailer()->sendEmailDoNotAcceptYardiPayment($tenant);

        echo 1;
    }

    /**
     * @return \RentJeeves\CoreBundle\Mailer\Mailer
     */
    protected function getMailer()
    {
        return $this->getContainer()->get('project.mailer');
    }
}
