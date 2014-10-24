<?php

namespace RentJeeves\ApiBundle\Forms;

use CreditJeeves\ApplicantBundle\Form\Type\UserAddressType as Base;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserAddressType extends Base
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('unit');
        $builder->remove('area');
        $builder->add('state', 'text', [
            'property_path' => 'area'
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation' => true,
            'data_class' => 'CreditJeeves\DataBundle\Entity\Address'
        ]);
    }
}
