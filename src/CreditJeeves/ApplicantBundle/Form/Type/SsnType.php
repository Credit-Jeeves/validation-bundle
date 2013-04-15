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

class SsnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->
            add(
                'ssn1',
                'text',
                array(
                    'label' => '',
                    )
            )->
            add(
                'ssn2',
                'text',
                array(
                    'label' => '',
                    )
            )->
            add(
                'ssn3',
                'text',
                array(
                    'label' => '',
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
        return 'creditjeeves_applicantbundle_ssntype';
    }
}
