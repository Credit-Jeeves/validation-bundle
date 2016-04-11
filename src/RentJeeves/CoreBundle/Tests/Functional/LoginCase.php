<?php

namespace RentJeeves\CoreBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class LoginCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldLoginTenantIfUsernameContainsEmail()
    {
        $this->load(true);

        $tenant = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);
        $tenant->setEmailField(null);
        $tenant->setEmailCanonical(null);
        $this->getEntityManager()->flush();

        $this->login('tenant11@example.com', 'pass');
    }
}
