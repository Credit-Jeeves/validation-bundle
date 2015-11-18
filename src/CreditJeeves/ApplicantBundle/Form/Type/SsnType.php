<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\SsnToPartsTransformer;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SsnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ssn1',
                'text',
                [
                    'label' => false,
                    'error_bubbling' => true,
                    'attr' => [
                        'class' => 'user-margin user-ssn1 user-ssn',
                        'maxlength' => 3,
                        'row_attr' => [
                            'class' => 'ssn_row'
                        ]
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Length([
                            'min' => 3,
                            'max' => 3
                        ]),
                    ],
                ]
            )
            ->add(
                'ssn2',
                'text',
                [
                    'error_bubbling' => true,
                    'attr' => [
                        'class' => 'user-margin user-ssn2 user-ssn',
                        'maxlength' => 2,
                    ],
                    'label' => false,
                ]
            )
            ->add(
                'ssn3',
                'text',
                [
                    'error_bubbling' => true,
                    'attr' => [
                        'class' => 'user-margin user-ssn3 user-ssn',
                        'maxlength' => 4,
                    ],
                    'label' => false,
                ]
            )
            ->addModelTransformer(
                new ReversedTransformer(
                    new SsnToPartsTransformer()
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
             'ssn1' => '',
             'ssn2' => '',
             'ssn3' => '',
        ]);
    }

    public function getName()
    {
        return 'ssn';
    }
}
