<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReturnedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('code', 'text', array('label' => 'Dealer Code'));
        $builder->add('email', 'email');
        $builder->add('first_name', 'text', array('label' => 'Name'));
        $builder->add('middle_initial', 'text');
        $builder->add('last_name', 'text');
        
        
//         $builder->add('ssn_1',         'text');
//         $builder->add('ssn_2',         'text');
//         $builder->add('ssn_3',         'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\User',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'intention' => 'username',
            )
        );
    }

    public function getName()
    {
        return 'returned';
    }
}
