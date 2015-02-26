<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use RentJeeves\TenantBundle\Form\DataTransformer\PhoneNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name', null, [
            'label' => 'name',
        ]);
        $builder->add('last_name');
        $builder->add(
            $builder->create('phone', 'text')->addViewTransformer(new PhoneNumberTransformer())
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'CreditJeeves\DataBundle\Entity\User',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'intention' => 'username',
                'validation_groups' => 'user_admin',
            ]
        );
    }

    public function getName()
    {
        return 'contact';
    }
}
