<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SsnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//         echo __METHOD__;
//         echo '<pre>';
//         print_r($options);
//         echo '</pre>';
        $ssn = $options['property_path'];
        //echo '>>>'.$ssn.'<<<';
//        $builder->add('ssn1', 'text');
//         $root = $builder->create('root', 'text', array('compound' => true));
         //$builder->add('ssn1', 'text', array('mapped' => false));
//         $root->add('ssn2', 'text');
//         $root->add('ssn3', 'text');
//         return $root->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                //'empty_data' => '*********',
                //'data_class' => 'CreditJeeves\DataBundle\Entity\User',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'intention' => 'ssn',
            )
        );
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_ssntype';
    }
}
