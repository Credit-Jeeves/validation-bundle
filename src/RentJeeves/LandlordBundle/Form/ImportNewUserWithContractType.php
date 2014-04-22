<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
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

    /**
     * @param Tenant $tenant
     * @param EntityManager $em
     * @param bool $token
     * @param bool $operation
     */
    public function __construct(
        Tenant $tenant,
        Unit $unit,
        ResidentMapping $residentMapping,
        EntityManager $em,
        Translator $translator
    ) {
        $this->tenant = $tenant;
        $this->unit = $unit;
        $this->residentMapping = $residentMapping;
        $this->em = $em;
        $this->translator = $translator;
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
                $this->tenant,
                $this->unit,
                $this->residentMapping,
                $this->em,
                $this->translator,
                $token = false,
                $useOperation = false
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
