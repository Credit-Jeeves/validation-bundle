<?php

namespace RentJeeves\TenantBundle\Services;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Traits\ValidateEntities;
use RentJeeves\CoreBundle\Services\PropertyProcess;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\Grouptype;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("invite.landlord")
 */
class InviteLandlord
{
    use ValidateEntities;

    protected $em;

    /**
     * @var Mailer
     */
    protected $mailer;

    protected $locale;

    /**
     * @var PropertyProcess
     */
    protected $propertyProcess;

    /**
     * @InjectParams({
     *     "em"        = @Inject("doctrine.orm.entity_manager"),
     *     "mailer"    = @Inject("project.mailer"),
     *     "locale"    = @Inject("%kernel.default_locale%"),
     *     "validator" = @Inject("validator"),
     *     "propertyProcess" = @Inject("property.process")
     * })
     */
    public function __construct(EntityManager $em, $mailer, $locale, $validator, $propertyProcess)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->locale = $locale;
        $this->validator = $validator;
        $this->propertyProcess = $propertyProcess;
    }

    public function invite(Invite $invite, $tenant)
    {
        $em = $this->em;
        $landlord = new Landlord();
        $contract = new Contract();
        $contract->setStatus(ContractStatus::PENDING);

        $landlordInDb = $em->getRepository('RjDataBundle:Landlord')->findOneBy(['email' => $invite->getEmail()]);

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

        $property = $invite->getProperty();
        $property->addPropertyGroup($group);
        if ($invite->getIsSingle()) {
            $this->propertyProcess->setupSingleProperty($property);
        } else {
            $contract->setSearch($invite->getUnitName());
        }
        $group->addGroupProperty($property);
        $contract->setProperty($property);
        $contract->setTenant($tenant);
        $contract->setHolding($holding);
        $contract->setGroup($group);

        $this->validate($contract);

        if ($this->hasErrors()) {
            return false;
        }

        $em->persist($property);
        $em->persist($group);
        $em->persist($contract);
        $em->persist($landlord);
        $em->flush();

        $this->mailer->sendRjLandLordInvite($landlord, $tenant, $contract);

        return $landlord;
    }
}
