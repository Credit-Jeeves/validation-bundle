<?php
namespace CreditJeeves\UserBundle\Form\Type;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\DataBundle\Form\ChoiceList\StateChoiceList;

class UserAddressType extends AbstractType
{
    /**
     * @var string
     */
    protected $type;

    public function __construct($type = 'applicant')
    {
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $extraOpts = [
            'area' => [
                'class' => ''
            ],
            'submit' => [
                'class' => 'button large green'
            ],
        ];

        if (in_array($this->type, ['tenant'])) {
            $extraOpts = [
                'area' => [
                    'class' => 'original'
                ],
                'submit' => [
                    'class' => 'button large'
                ],
            ];
        }

        /** @var $address Address */
        $address = isset($options['data']) ? $options['data'] : new Address();

        $builder->add(
            'street',
            'text',
            [
                'label' => 'settings.address.form.street'
            ]
        );
        $builder->add(
            'city',
            'text',
            [
                'label' => 'settings.address.form.city'
            ]
        );
        $builder->add(
            'area',
            'choice',
            [
                'label' => 'settings.address.form.area',
                'choice_list' => new StateChoiceList(),
                'attr' => [
                    'class' => $extraOpts['area']['class'],
                ]
            ]
        );
        $builder->add(
            'zip',
            'text',
            [
                'label' => 'settings.address.form.zip'
            ]
        );
        $builder->add(
            'unit',
            'text',
            [
                'required' => false,
                'label' => 'settings.address.form.unit'
            ]
        );

        if (true == $address->getIsDefault()) {
            $builder->add('isDefault', 'hidden');
        } else {
            $builder->add(
                'isDefault',
                'checkbox',
                [
                    'required' => false,
                    'label' => 'settings.address.form.isdefault',
                    'attr' => [
                        'no_box' => true
                    ]
                ]
            );
        }

        $builder->add(
            'submit',
            'submit',
            [
                'label' => (null == $address->getId()) ? 'common.add' : 'common.save',
                'attr' => [
                    'class' => $extraOpts['submit']['class']
                ]
            ]
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'cascade_validation' => true,
                'data_class' => 'CreditJeeves\DataBundle\Entity\MailingAddress',
                'validation_groups' => array('user_address_new'),
            )
        );
    }

    public function getName()
    {
        return 'creditjeeves_userbundle_useraddresstype';
    }
}
