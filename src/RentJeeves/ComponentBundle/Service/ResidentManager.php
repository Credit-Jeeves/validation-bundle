<?php

namespace RentJeeves\ComponentBundle\Service;

use CreditJeeves\DataBundle\Entity\Holding;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Validator\Validator;
use CreditJeeves\CoreBundle\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 *
 * @DI\Service("resident_manager")
 */
class ResidentManager
{

    protected $supportEmail;

    protected $validator;

    protected $em;

    protected $translator;

    /**
     * @DI\InjectParams({
     *      "validator"          = @DI\Inject("validator"),
     *      "translator"         = @DI\Inject("translator"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "supportEmail"       = @DI\Inject("%support_email%")
     * })
     *
     * @access public
     */
    public function __construct(Validator $validator, Translator $translator, EntityManager $em, $supportEmail)
    {
        $this->supportEmail = $supportEmail;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * @param ResidentMapping $residentMapping
     * @return array
     */
    public function validate(Landlord $landlord, ResidentMapping $residentMapping = null)
    {
        $errors = [];

        if (!$residentMapping) {
            return $errors;
        }


        $errorsResidentMapping = $this->validator->validate($residentMapping, ['add_or_edit_tenants']);
        foreach ($errorsResidentMapping as $error) {
            $errors[] = $this->translator->trans($error->getMessage());
        }

        if (!empty($errors)) {
            return $errors;
        }

        /**
         * @var $errorsResidentMapping ConstraintViolationList
         */
        $errorsResidentMapping = $this->validator->validate($residentMapping, ['unique_entity']);
        $existingMapping = $this->getExistingMapping($residentMapping);
        if ($errorsResidentMapping->count() === 1 &&
            ($existingMapping->getTenant()->getEmail() !== $residentMapping->getTenant()->getEmail())
        ) {
            $errors[] = $this->translator->trans(
                $errorsResidentMapping->get(0)->getMessage(),
                array(
                    '%support_email%'   => $this->supportEmail,
                    '%email%'           => $existingMapping->getTenant()->getEmail()
                )
            );
        }



        if (empty($errors)) {
            $this->clearWaitingRoom($landlord, $residentMapping);
            $this->em->persist($residentMapping);
        }

        return $errors;
    }

    /**
     * @param Tenant $tenant
     * @param Holding $landlordHolding
     * @return bool
     */
    public function hasMultipleContracts(Tenant $tenant, Holding $landlordHolding)
    {
        $residentMapping = $tenant->getResidentForHolding($landlordHolding);

        if (empty($residentMapping)) {
            return false;
        }

        $contracts = $this->em->getRepository('RjDataBundle:Tenant')->getContractsByHoldingAndResident(
            $residentMapping,
            $landlordHolding
        );

        if (count($contracts) > 1) {
            return true;
        }

        return false;
    }

    /**
     * @param Landlord $landlord
     * @param ResidentMapping $residentMapping
     */
    protected function clearWaitingRoom(Landlord $landlord, ResidentMapping $residentMapping)
    {
        foreach ($landlord->getGroups() as $group) {
            $this->em->getRepository('RjDataBundle:ContractWaiting')->clearResidentContracts(
                $residentMapping->getResidentId(),
                $group->getId()
            );
        }
    }

    /**
     * @param ResidentMapping $residentMapping
     * @return ResidentMapping
     */
    protected function getExistingMapping(ResidentMapping $residentMapping)
    {
        /**
         * @var $residentMapping ResidentMapping
         */
        $residentMapping = $this->em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
            array(
                'holding' => $residentMapping->getHolding()->getId(),
                'residentId' => $residentMapping->getResidentId(),
            )
        );

        return $residentMapping;
    }
}
