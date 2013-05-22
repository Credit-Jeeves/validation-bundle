<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\DataBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CheckoutAuthorizeNetAimType extends AbstractType
{
    /**
     * @var User
     */
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $months = array_combine(
            array('') + range(0, 12),
            array('Month') + range(0, 12)
        );
        $years = array_combine(
            array('') + range(date('y') - 1, date('y') + 12),
            array('Year') + range(date('Y') - 1, date('Y') + 12)
        );


        $builder
            ->add(
                'first_name',
                'text',
                array(
                    'data' => $this->user->getFirstName()
                )
            )
            ->add(
                'last_name',
                'text',
                array(
                    'data' => $this->user->getLastName()
                )
            )
            ->add(
                'address1',
                'text',
                array(
                    'data' => $this->user->getStreetAddress1()
                )
            )
            ->add(
                'address2',
                'text',
                array(
                    'data' => $this->user->getStreetAddress2()
                )
            )
            ->add(
                'state',
                'text',
                array(
                    'data' => $this->user->getState()
                )
            )
            ->add(
                'zip',
                'text',
                array(
                    'data' => $this->user->getZip()
                )
            )
            ->add('card_number')
            ->add('card_csc')
            ->add(
                'card_expiration_date_month',
                'choice',
                array(
                    'choices' => $months
                )
            )
            ->add(
                'card_expiration_date_year',
                'choice',
                array(
                    'choices' => $years
                )
            );

//            $builder->add(
//                'OutWalletAnswer' . $i,
//                'choice',
//                array(
//                    'choices' => $answers,
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => true,
//                    'label' => $question
//                )
//            );
//            $this->setValidator(
//                'OutWalletAnswer' . $i,
//                new sfValidatorChoice(
//                    array(
//                        'choices' => array_keys($answers),
//                        'required' => true
//                    )
//                )
//            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
//                'intention' => 'username',
            )
        );
    }

    public function getName()
    {
        return 'checkout_authorize_net_aim_type';
    }
}
