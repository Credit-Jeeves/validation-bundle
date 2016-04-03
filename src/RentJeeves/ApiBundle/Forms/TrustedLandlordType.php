<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use RentJeeves\DataBundle\Enum\TrustedLandlordType as TrustedLandlordTypeEnum;

class TrustedLandlordType extends AbstractType
{
    const NAME = 'landlord';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('email', 'email');
        $builder->add(
            'type',
            'choice',
            [
                'choices' => TrustedLandlordTypeEnum::cachedTitles(),
            ]
        );
        $builder->add('first_name', 'text', ['property_path' => 'firstName']);
        $builder->add('last_name', 'text', ['property_path' => 'lastName']);
        $builder->add('company_name', 'text', ['property_path' => 'companyName']);
        $builder->add('phone');
        $builder->add('mailing_address', new MailingAddressType(),['data_class' => $options['data_class']]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO',
            'csrf_protection' => false,
            'cascade_validation' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
