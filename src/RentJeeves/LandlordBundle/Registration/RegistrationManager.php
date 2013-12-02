<?php

namespace RentJeeves\LandlordBundle\Registration;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Enum\GroupType;
use Doctrine\ORM\EntityManager;
use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Unit;
use Symfony\Component\Form\Form;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("landlord.registration")
 */
class RegistrationManager
{
    /** @var  EntityManager */
    protected $em;
    protected $passwordEncoder;
    protected $defaultLocale;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "passwordEncoder" = @DI\Inject("user.security.encoder.digest"),
     *     "locale" = @DI\Inject("%kernel.default_locale%")
     * })
     */
    public function __construct($em, $passwordEncoder, $locale)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->defaultLocale = $locale;
    }

    public function register(Form $form, array $formData)
    {
        /** @var Landlord $landlord */
        $landlord = $form->getData()['landlord'];
        /** @var Address $address */
        $address = $form->getData()['address'];
        $address->setUser($landlord);
        $landlord->addAddress($address);

        $password = $this->passwordEncoder
            ->encodePassword($formData['landlord']['password']['Password'], $landlord->getSalt());

        $landlord->setPassword($password);
        $landlord->setCulture($this->defaultLocale);

        $holding = new Holding();
        $holding->setName($landlord->getUsername());
        $landlord->setHolding($holding);
        $group = new Group();
        $group->setType(GroupType::RENT);
        $group->setName($landlord->getUsername());
        $group->setHolding($holding);
        $holding->addGroup($group);
        $landlord->setAgentGroups($group);

        $property = $this->em->getRepository('RjDataBundle:Property')->find($formData['property']);
        if ($property) {
            $units = (isset($formData['units']))? $formData['units'] : array();
            $property->addPropertyGroup($group);
            $group->addGroupProperty($property);
            if (!empty($units)) {
                foreach ($units as $name) {
                    if (empty($name)) {
                        continue;
                    }
                    $unit = new Unit();
                    $unit->setProperty($property);
                    $unit->setHolding($holding);
                    $unit->setGroup($group);
                    $unit->setName($name);
                    $this->em->persist($unit);
                }
            }
        }

        $this->em->persist($address);
        $this->em->persist($holding);
        $this->em->persist($group);
        $this->em->persist($landlord);
        $this->em->flush();

        return $landlord;
    }

}
