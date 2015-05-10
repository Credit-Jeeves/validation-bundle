<?php

namespace RentJeeves\ApiBundle\Forms;

use CreditJeeves\ApplicantBundle\Form\Type\UserAddressType as Base;
use RentJeeves\ApiBundle\Forms\DataTransformer\StreetTransformerListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAddressType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('unit');
        $builder->remove('area');
        $builder->add('state', 'text', [
            'property_path' => 'area'
        ]);
        $builder->add('number', 'text', [
            'constraints' => [
                new NotBlank([
                    'message' => 'api.errors.property.number.empty',
                    'groups'  => ['user_address_new']
                ]),
            ]
        ]);

        $builder->addEventSubscriber(new StreetTransformerListener());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation' => true,
            'data_class' => 'CreditJeeves\DataBundle\Entity\Address'
        ]);
    }
}
