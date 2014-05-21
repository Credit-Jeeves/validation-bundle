<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\Property;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use CreditJeeves\CoreBundle\Translation\Translator;

/**
 * This form for new Tenant, new Contract
 *
 * Class ImportNewUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportNewUserWithContractType extends AbstractType
{

    protected $tenant;

    protected $unit;

    protected $residentMapping;

    protected $em;

    protected $translator;

    protected $isMultipleProperty;
    /**
     * @param Tenant $tenant
     * @param EntityManager $em
     * @param bool $token
     * @param bool $operation
     */
    public function __construct(
        EntityManager $em,
        Translator $translator,
        ResidentMapping $residentMapping,
        Tenant $tenant,
        Unit $unit = null,
        $isMultipleProperty = false
    ) {
        $this->tenant = $tenant;
        $this->unit = $unit;
        $this->residentMapping = $residentMapping;
        $this->em = $em;
        $this->translator = $translator;
        $this->isMultipleProperty = $isMultipleProperty;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'tenant',
            new ImportTenantType(),
            array()
        );

        $builder->add(
            'contract',
            new ImportContractType(
                $this->em,
                $this->translator,
                $this->tenant,
                $this->residentMapping,
                $this->unit,
                $token = false,
                $useOperation = false,
                $this->isMultipleProperty
            ),
            array()
        );

        $builder->add(
            'sendInvite',
            'checkbox',
            array(
                'data'      => true,
                'required'  => false,
            )
        );

        $builder->add(
            '_token',
            'hidden',
            array(
                'required'  => true
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'       => false,
                'cascade_validation'    => true,
                'validation_groups' => array(
                    'import',
                ),
            )
        );
    }

    public function getName()
    {
        return 'import_new_user_with_contract';
    }
}
