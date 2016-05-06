<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Model\Import;
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
    const NAME = 'import_new_user_with_contract';

    protected $import;

    protected $em;

    protected $translator;

    protected $isMultipleProperty;

    /**
     * @var boolean
     */
    protected $isSupportResidentId;

    /**
     * @param EntityManager $em
     * @param Translator $translator
     * @param Import $import
     * @param bool $isMultipleProperty
     * @param bool $isSupportResidentId
     */
    public function __construct(
        EntityManager $em,
        Translator $translator,
        Import $import,
        $isMultipleProperty = false,
        $isSupportResidentId = true
    ) {
        $this->import = $import;
        $this->em = $em;
        $this->translator = $translator;
        $this->isMultipleProperty = $isMultipleProperty;
        $this->isSupportResidentId = $isSupportResidentId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'tenant',
            new ImportTenantType($this->em, $this->translator),
            []
        );

        $builder->add(
            'contract',
            new ImportContractType(
                $this->em,
                $this->translator,
                $this->import,
                $token = false,
                $this->isMultipleProperty,
                $sendInvite = false,
                $this->isSupportResidentId
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
        return self::NAME;
    }
}
