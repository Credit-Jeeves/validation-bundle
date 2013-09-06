<?php

namespace RentJeeves\TenantBundle\Service;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("invite.landord")
 */
class InviteLandlord
{

    protected $em;

    protected $mailer;

    protected $locale;

    /**
     * @InjectParams({
     *     "em"     = @Inject("doctrine.orm.entity_manager"),
     *     "mailer" = @Inject("creditjeeves.mailer"),
     *     "locale" = @Inject("%kernel.default_locale%"),
     * })
     */
    public function __construct(EntityManager $em, $mailer, $locale)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->locale = $locale;
    }

    public function invite($invite, $tenant)
    {
        $em = $this->em;
        $landlord = new Landlord();
        $contract = new Contract();

        $landlordInDb = $em->getRepository('RjDataBundle:Landlord')->findOneBy(
            array(
                'email' => $invite->getEmail(),
            )
        );

        if ($landlordInDb) {
            unset($landlord);
            $landlord = $landlordInDb;
            $groups = $landlord->getGroups();
            if ($landlordInDb->getIsActive() && $landlord->hasMerchant()) {
                $contract->setStatus(ContractStatus::PENDING);
            } else {
                $contract->setStatus(ContractStatus::INVITE);
            }
            $holding = $landlord->getHolding();
            $group = $landlord->getCurrentGroup();
        } else {
            $contract->setStatus(ContractStatus::INVITE);
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
            $group->setHolding($holding);
            $holding->addGroup($group);
            $landlord->setAgentGroups($group);
            $em->persist($group);
            $em->persist($holding);
        }

        $unit = new Unit();
        $unit->setName($invite->getUnit());
        $unit->setProperty($invite->getProperty());
        $unit->setHolding($holding);
        $unit->setGroup($group);

        $contract->setProperty($invite->getProperty());
        $contract->setUnit($unit);
        $contract->setTenant($tenant);
        $contract->setHolding($holding);
        $contract->setGroup($group);

        $em->persist($unit);
        $em->persist($contract);
        $em->persist($landlord);
        $em->flush();

        $this->mailer->sendRjLandLordInvite($landlord, $tenant, $contract);

        return $landlord;
    }
}
