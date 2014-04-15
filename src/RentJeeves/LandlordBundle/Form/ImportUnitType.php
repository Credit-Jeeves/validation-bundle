<?php

namespace RentJeeves\LandlordBundle\Form;

use CreditJeeves\DataBundle\Entity\Operation;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Accounting\AccountingImport;
use RentJeeves\LandlordBundle\Form\Type\ViewType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use CreditJeeves\CoreBundle\Translation\Translator;

/**
 * This form for Unit
 *
 * Class ImportUnitType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportUnitType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            new ViewType(),
            array(
                'error_bubbling' => false,
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Unit',
                'validation_groups' => array(
                    'import',
                ),
                'csrf_protection'    => false,
            )
        );
    }

    public function getName()
    {
        return 'import_unit';
    }
}
