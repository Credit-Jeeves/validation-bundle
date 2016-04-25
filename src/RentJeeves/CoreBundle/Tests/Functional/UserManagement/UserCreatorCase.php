<?php

namespace RentJeeves\CoreBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class UserCreatorCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateUserWithoutEmailIfDbDoesNotContainUserWithSameUserName()
    {
        $this->load(true);

        $userCreator = $this->getContainer()->get('renttrack.user_creator');
        $userCreator->createTenant('test', 'test');

        $user = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->findOneBy([], ['id' => 'DESC']);

        $this->assertEquals('testtest', $user->getUsernameCanonical(), 'User has incorrect username');
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            ['test', 'testtest1'],
            ['test9', 'testtest10'],
            ['test10', 'testtest11'],
            ['test33', 'testtest34'],
            ['test99', 'testtest100'],
            ['test100', 'testtest101'],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function shouldIncreaseNumberForUserNameIfDbContainsUserWithSameUserName($oldUserName, $newUserName)
    {
        $this->load(true);

        $userCreator = $this->getContainer()->get('renttrack.user_creator');
        $userCreator->createTenant('test', $oldUserName);

        $userCreator->createTenant('test', 'test');
        $user = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->findOneBy([], ['id' => 'DESC']);

        $this->assertEquals($newUserName, $user->getUsernameCanonical(), 'User has incorrect username');
    }
}
