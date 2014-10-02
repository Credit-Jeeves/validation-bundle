<?php

namespace RentJeeves\ApiBundle\Services;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\Grouptype;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;

/**
 * @DI\Service("api.invite.landlord")
 */
class InviteLandlord
{

    protected $em;

    /**
     * @var Mailer
     */
    protected $mailer;

    protected $locale;

    /**
     * @DI\InjectParams({
     *     "em"     = @DI\Inject("doctrine.orm.entity_manager"),
     *     "mailer" = @DI\Inject("project.mailer"),
     *     "locale" = @DI\Inject("%kernel.default_locale%"),
     * })
     */
    public function __construct(EntityManager $em, $mailer, $locale)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->locale = $locale;
    }

    public function invite(Invite $invite, $tenant)
    {
        $em = $this->em;
        $landlord = new Landlord();
        $contract = new Contract();
        /** @var Landlord $landlordInDb */
        $landlordInDb = $em->getRepository('RjDataBundle:Landlord')
            ->findOneBy([ 'email' => $invite->getEmail() ]);
        $contract->setStatus(ContractStatus::PENDING);
        if ($landlordInDb) {
            unset($landlord);
            $landlord = $landlordInDb;
            $holding = $landlord->getHolding();
            $group = $landlord->getCurrentGroup();
        } else {
            $landlord->setPassword(md5(md5(1)));
            $landlord->setFirstName($invite->getFirstName());
            $landlord->setLastName($invite->getLastName());
            $landlord->setPhone($invite->getPhone());
            $landlord->setEmail($invite->getEmail());
            $landlord->setCulture($this->locale);
            $holding = new Holding();
            $holding->setName($landlord->getUsername());
            $landlord->setHolding($holding);
            $group = new Group();
            $group->setName($landlord->getUsername());
            $group->setType(GroupType::RENT);
            $group->setHolding($holding);
            $holding->addGroup($group);
            $landlord->setAgentGroups($group);
            $em->persist($group);
            $em->persist($holding);
            $em->flush();
        }

        if ($unit = $invite->getUnit()) {
            $property = $unit->getProperty();
            if (!$property->getIsSingle()) {
                $contract->setSearch($unit->getName());
            }
            $group->addGroupProperty($property);
            $contract->setProperty($property);
        }

        $contract->setTenant($tenant);
        $contract->setHolding($holding);
        $contract->setGroup($group);

        $em->persist($group);
        $em->persist($contract);
        $em->persist($landlord);
        
        $em->flush();

        $this->mailer->sendRjLandLordInvite($landlord, $tenant, $contract);

        return $landlord;
    }
}
