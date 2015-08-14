<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\ApplicantBundle\Form\Type\SsnType;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;

class UserDetailsType extends AbstractType
{
    /**
     * @var Tenant
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            'text',
            array(
                'error_bubbling' => true,
                'label' => 'First Name'
            )
        );
        $builder->add(
            'last_name',
            'text',
            array(
                'error_bubbling' => true,
                'label' => 'Last Name'
            )
        );
        $builder->add(
            'date_of_birth',
            'date',
            array(
                'label' => 'common.date_of_birth',
                'format' => 'MMddyyyy',
                'years' => range(date('Y') - 110, date('Y')),
//                'empty_value' => array( // TODO fix validation problem (error message processing)
//                    'year' => 'Year',
//                    'month' => 'Month',
//                    'day' => 'Day',
//                ),
                'attr' => array(
                    'class' => 'original',
                    'html' => '<div class="tooltip-box type3 pie-el">' .
                        '<p class="verify" data-bind="i18n: {}">checkout.date_of_birth.tooltip.line1</p>' .
                        '</div>'
                ),
                'invalid_message' => 'checkout.error.date_of_birth.invalid'
            )
        );
        $builder->add(
            'ssn',
            new SsnType(),
            array(
                'label' => 'common.ssn',
            )
        );

        $builder->add(
            'address_choice',
            'entity',
            array(
                'class' => 'CreditJeeves\DataBundle\Entity\Address',
                'mapped' => false,
                'label' => 'address.on_credit_file',
                'expanded' => true,
                'choices' => $this->user->getAddresses(),
                'attr' => array(
                    'data-bind' => 'checked: address.addressChoice',
                    'html' =>
                        '<!-- ko foreach: newAddresses -->' .
                            '<label class="checkbox radio">' .
                                '<input type="radio" name="' . $this->getName() . '[address_choice]"' .
                                    'required="required"' .
                                    'data-bind="' .
                                    'checked: $parent.address.addressChoice, ' .
                                    'attr: {id: \'' . $this->getName() . '_address_choice_\' + $data.id() }, ' .
                                    'value: $data.id()' .
                                    '" />' .
                                '<i></i>' .
                                '<span data-bind="text: $data.toString()"></span>' .
                            '</label>' .
                        '<!-- /ko -->' .
                        '<div class="fields-box" data-bind="visible: !address.isAddNewAddress()">' .
                            '<a href="#" data-bind="i18n: {}, click: address.addAddress">common.add_new</a>' .
                        '</div>'
                ),
                'invalid_message' => 'checkout.error.address_choice.invalid',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('address_choice'),
                            'message' => 'checkout.error.address_choice.empty',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'is_new_address',
            'hidden',
            array(
                'mapped' => false,
                'attr' => array(
                    'data-bind' => 'value: address.isAddNewAddress',
                )
            )
        );
        $builder->add(
            'new_address',
            new UserAddressType(),
            array(
                'mapped' => false,
                'label' => false,
                'by_reference' => true,
                'attr' => array(
                    'no_box' => true,
                    'force_row' => true,
                    'row_attr' => array(
                        'data-bind' => 'visible: address.isAddNewAddress'
                    )
                )
            )
        );

        $builder->add('submit', 'submit', array('attr' => array('force_row' => true, 'class' => 'hide_submit')));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
                'validation_groups' => function (FormInterface $form) {
                    $groups = array('birth_and_ssn','authentication');
                    if ('false' == $form->get('is_new_address')->getData()) {
                        $groups[] = 'address_choice';
                    }
                    if ('true' == $form->get('is_new_address')->getData()) {
                        $groups[] = 'user_address_new';
                    }

                    return $groups;
                }
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_checkoutbundle_userdetailstype';
    }
}
