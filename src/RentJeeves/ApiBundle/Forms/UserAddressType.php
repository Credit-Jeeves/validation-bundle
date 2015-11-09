<?php

namespace RentJeeves\ApiBundle\Forms;

use CreditJeeves\ApplicantBundle\Form\Type\UserAddressType as Base;
use RentJeeves\ApiBundle\Forms\DataTransformer\StreetTransformerListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAddressType extends Base
{
    const NAME = '';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

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
        $builder->add('is_current', 'checkbox', [
            'property_path' => 'is_default'
        ]);

        $builder->addEventSubscriber(new StreetTransformerListener());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'CreditJeeves\DataBundle\Entity\MailingAddress',
            'cascade_validation' => true,
            'validation_groups' => ['user_address_new'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
