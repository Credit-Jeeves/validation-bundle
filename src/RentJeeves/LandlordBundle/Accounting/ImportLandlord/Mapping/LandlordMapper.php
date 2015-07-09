<?php

namespace RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException;

/**
 * @method Landlord map(array $data, Group $group = null)
 */
class LandlordMapper extends AbstractMapper
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @throws MappingException
     *
     * @return Landlord
     */
    protected function mapObject()
    {
        if (null === $group = $this->getGroup()) {
            throw new \LogicException('Please send the group as 2nd parameter for function map');
        }

        $holding = $group->getHolding();
        if (null !== $holding->getId() && $landlord = $this->getLandlordRepository()->findOneBy(
                [
                    'holding' => $holding,
                    'externalLandlordId' => $this->get('landlordID')
                ]
            )
        ) {
            return $landlord;
        }

        return $this->createLandlord();
    }

    /**
     * @return Landlord
     */
    protected function createLandlord()
    {
        $newLandlord = new Landlord();
        $newLandlord->setHolding($this->getGroup()->getHolding());
        $newLandlord->setFirstName($this->get('first_name'));
        $newLandlord->setLastName($this->get('last_name'));
        $newLandlord->setEmail($this->get('ll_email'));
        $newLandlord->setPhone($this->get('ll_phone'));
        $newLandlord->setPassword(md5(rand(1, 99999)));
        $newLandlord->setCulture($this->locale);
        $newLandlord->setAgentGroups($this->getGroup());
        $newLandlord->setExternalLandlordId($this->get('landlordID'));

        $this->em->persist($newLandlord);

        return $newLandlord;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\LandlordRepository
     */
    protected function getLandlordRepository()
    {
        return $this->em->getRepository('RjDataBundle:Landlord');
    }
}
