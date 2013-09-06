<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

use CreditJeeves\DataBundle\Form\ChoiceList\StateChoiceList;
use CreditJeeves\ApplicantBundle\Form\Type\SsnType;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\SsnToPartsTransformer;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'street',
            'text',
            array(
                'label' => 'Address',
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'all-width',
                    'placeholder' => 'Street' // TODO it transalteble
                )
            )
        );
        $builder->add(
            'unit',
            'text',
            array(
                'label' => false
            )
        );
        $builder->add(
            'city',
            'text',
            array(
                'label' => false,
                'attr' => array(
                   'class' => 'city-width',
                    'placeholder' => 'City' // TODO it transalteble
                ),
                'error_bubbling' => true,
            )
        );
        $builder->add(
            'area',
            'choice',
            array(
                'label' => false,
                'error_bubbling' => true,
                'choice_list' =>  new StateChoiceList(),
                'required' => true,
                'attr' => array(
                    'class' => 'original'
                )
            )
        );
        $builder->add(
            'zip',
            'text',
            array(
                'label' => false,
                'attr' => array(
                    'class' => 'zc-width',
                    'placeholder' => 'Zip Code' // TODO it transalteble
                ),
                'error_bubbling' => true,
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\Address',
                'validation_groups' => array(),
            )
        );
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_useraddresstype';
    }
}
