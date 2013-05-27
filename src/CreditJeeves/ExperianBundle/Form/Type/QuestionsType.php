<?php
namespace CreditJeeves\ExperianBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QuestionsType extends AbstractType
{
    /**
     * @var array
     */
    protected $questions;

    public function __construct(array $questions)
    {
        $this->questions = $questions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->add(
//            'phone_type',
//            'choice',
//            array(
//                'choices' => array(
//                    'Mobile',
//                    'Home',
//                    'Work'
//                )
//            )
//        );
//        $builder->add(
//            'phone',
//            'text'
//        );

        $i = 1;
        foreach ($this->questions as $question => $answers) {
            $builder->add(
                'OutWalletAnswer' . $i,
                'choice',
                array(
                    'choices' => $answers,
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                    'label' => $question
                )
            );
//            $this->setValidator(
//                'OutWalletAnswer' . $i,
//                new sfValidatorChoice(
//                    array(
//                        'choices' => array_keys($answers),
//                        'required' => true
//                    )
//                )
//            );

            $i++;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
//                'data_class' => 'CreditJeeves\DataBundle\Entity\User',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
//                'intention' => 'username',
            )
        );
    }

    public function getName()
    {
        return 'questions';
    }
}
