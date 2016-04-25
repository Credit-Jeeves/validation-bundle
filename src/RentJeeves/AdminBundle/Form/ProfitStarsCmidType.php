<?php
namespace RentJeeves\AdminBundle\Form;

use RentJeeves\DataBundle\Entity\ProfitStarsCmid;
use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfitStarsCmidType extends Base
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cmid', 'text', ['required' => false, 'label' => 'cmid']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'cascade_validation'    => true,
                'data_class'            => ProfitStarsCmid::class
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rentjeeves_adminbundle_profitstars_cmid';
    }
}
