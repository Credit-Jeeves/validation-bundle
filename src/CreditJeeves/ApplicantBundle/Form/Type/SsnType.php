<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\SsnToPartsTransformer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ReversedTransformer;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SsnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->
            add(
                'ssn1',
                'text',
                array(
                    'label' => false,
                    'error_bubbling' => true,
                    'attr' => array(
                        'class' => 'user-margin user-ssn1 user-ssn',
                        'maxlength' => 3
                    ),
                    'constraints' => array(
                        new NotBlank(),
                        new Length(
                            array(
                                'min' => 3,
                                'max' => 3
                            )
                        ),
                    ),
                )
            )->
            add(
                'ssn2',
                'text',
                array(
                    'attr' => array(
                        'class' => 'user-margin user-ssn2 user-ssn',
                        'maxlength' => 2
                    ),
                    'label' => false,
                    )
            )->
            add(
                'ssn3',
                'text',
                array(
                    'attr' => array(
                        'class' => 'user-margin user-ssn3 user-ssn',
                        'maxlength' => 4
                    ),
                    'label' => false,
                    )
            )->addModelTransformer(
                new ReversedTransformer(
                    new SsnToPartsTransformer()
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                 'ssn1' => '',
                 'ssn2' => '',
                 'ssn3' => '',
            )
        );
    }

    public function getName()
    {
        return 'ssn';
    }
}
