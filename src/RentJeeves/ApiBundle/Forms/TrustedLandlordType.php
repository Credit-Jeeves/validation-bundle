<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\TenantBundle\Form\DataTransformer\PhoneNumberTransformer;
use RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormInterface;
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
        $builder->add(
            'type',
            'choice',
            [
                'choices' => TrustedLandlordTypeEnum::cachedTitles(),
                'invalid_message' => 'api.errors.landlord.type.invalid',
            ]
        );
        $builder->add('first_name', 'text', [
            'property_path' => 'firstName',
            'required' => false,
            'description' => 'Required for type "person" of landlord.'
        ]);
        $builder->add('last_name', 'text', [
            'property_path' => 'lastName',
            'required' => false,
            'description' => 'Required for type "person" of landlord.'
        ]);
        $builder->add('company_name', 'text', [
            'property_path' => 'companyName',
            'required' => false,
            'description' => 'Required for type "company" of landlord.'
        ]);
        $builder->add(
            $builder->create('phone', 'text', ['required' => false])->addViewTransformer(new PhoneNumberTransformer())
        );
        $builder->add('email', 'email', ['required' => false]);
        $builder->add('mailing_address', new MailingAddressType(), ['data_class' => $options['data_class']]);
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
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = $form->getRoot()->getConfig()->getOption('validation_groups');
                if (is_callable($validationGroups)) {
                    $validationGroups = call_user_func($validationGroups, $form->getRoot());
                }
                if (is_array($validationGroups) && in_array('new_unit', $validationGroups)) {
                    $groups[] = 'trusted_landlord';
                    if ($type = $form->get('type')->getData()) {
                        $groups[] = $type;
                    }

                    return $groups;
                }

                return [];
            }
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
