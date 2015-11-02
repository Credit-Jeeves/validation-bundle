<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\DataBundle\Form\ChoiceList\StateChoiceList;

class UserAddressType extends AbstractType
{
    /**
     * @var string
     */
    protected $koPrefix;

    public function __construct($koPrefix = '')
    {
        $this->koPrefix = $koPrefix;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'street',
            'text',
            array(
                'label' => 'common.address',
                'attr' => array(
                    'class' => 'all-width',
                    'placeholder' => 'common.street',
                    'data-bind' => "value: {$this->koPrefix}address.street"
                )
            )
        );
        $builder->add(
            'unit',
            'text',
            array(
                'label' => false,
                'required' => false
            )
        );
        $builder->add(
            'city',
            'text',
            array(
                'label' => false,
                'attr' => array(
                   'class' => 'city-width',
                    'placeholder' => 'common.city',
                    'data-bind' => "value: {$this->koPrefix}address.city"
                ),
            )
        );
        $builder->add(
            'area',
            'choice',
            array(
                'label' => false,
                'choice_list' =>  new StateChoiceList(),
                'required' => true,
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => "value: {$this->koPrefix}address.area"
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
                    'placeholder' => 'common.zip_code',
                    'data-bind' => "value: {$this->koPrefix}address.zip"
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'CreditJeeves\DataBundle\Entity\MailingAddress',
                'validation_groups' => array('user_address_new'),
            )
        );
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_useraddresstype';
    }
}
