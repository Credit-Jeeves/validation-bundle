<?php
namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TenantBundle\Service\InviteLandlord;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class DepositAccountCase extends BaseTestCase
{
    /**
     * @test
     */
    public function prePersistTest()
    {
        $this->load(true);
        $container = static::getContainer();
        $inviteLandlordService = $container->get('invite.landord');
        $em = $container->get('doctrine.orm.entity_manager');

        $this->assertTrue($inviteLandlordService instanceof InviteLandlord);
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com',
            )
        );
        $property = $em->getRepository('RjDataBundle:Property')->findOneBy(
            array(
                'jb' => '40.7316721',
                'kb' => '-73.9917422',
            )
        );
        $this->assertTrue($tenant instanceof Tenant);
        $this->assertTrue($property instanceof Property);

        $invite = new Invite();
        $invite->setEmail('example@mail.com');
        $invite->setUnit('1A');
        $invite->setFirstName('Ivan');
        $invite->setLastName('Drachka');
        $invite->setPhone('109813313');
        $invite->setProperty($property);
        $invite->setTenant($tenant);

        $em->persist($invite);
        $em->flush();

        $landord = $inviteLandlordService->invite($invite, $tenant);
        $this->assertTrue($landord instanceof Landlord);

        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'property' => $property->getId(),
                'tenant'   => $tenant->getId(),
            )
        );

        $this->assertTrue($contract instanceof Contract);
        $this->assertTrue($contract->getStatus() === ContractStatus::INVITE);

        $depositAccount = new DepositAccount();
        $depositAccount->setMerchantName('My First merchantName');
        $depositAccount->setGroup($landord->getCurrentGroup());

        $em->persist($depositAccount);
        $em->flush();

        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'property' => $property->getId(),
                'tenant'   => $tenant->getId(),
            )
        );

        $this->assertTrue($contract instanceof Contract);
        $this->assertTrue($contract->getStatus() === ContractStatus::PENDING);
    }
}
