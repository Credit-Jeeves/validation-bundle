<?php

namespace RentJeeves\TenantBundle\Services;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\Grouptype;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("invite.landlord")
 */
class InviteLandlord
{
    private $isNew = true;

    protected $em;

    /**
     * @var Mailer
     */
    protected $mailer;

    protected $locale;

    /**
     * @InjectParams({
     *     "em"     = @Inject("doctrine.orm.entity_manager"),
     *     "mailer" = @Inject("project.mailer"),
     *     "locale" = @Inject("%kernel.default_locale%"),
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

        $landlordInDb = $em->getRepository('RjDataBundle:Landlord')->findOneBy(
            array(
                'email' => $invite->getEmail(),
            )
        );
        $contract->setStatus(ContractStatus::PENDING);
        if ($landlordInDb) {
            unset($landlord);
            $this->isNew = false;
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
            $isSingleProperty = $property->isSingle();
            $unitName = $unit->getName();
        } else {
            $property = $invite->getProperty();
        }

        if ($property) {
            // Create contract only if we have property
            isset($isSingleProperty) || $isSingleProperty = $invite->getIsSingle();
            if ($isSingleProperty) {
                $property->setIsSingle(true);
                $em->flush($property);
            } else {
                isset($unitName) || $unitName = $invite->getUnitName();
                $contract->setSearch($unitName);
            }

            $group->addGroupProperty($property);
            $contract->setProperty($property);
            $contract->setTenant($tenant);
            $contract->setHolding($holding);
            $contract->setGroup($group);
        }

        $em->persist($group);
        !$property || $em->persist($contract);
        $em->persist($landlord);

        $em->flush();

        $this->mailer->sendRjLandLordInvite($landlord, $tenant, $contract);

        return $landlord;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }
}
