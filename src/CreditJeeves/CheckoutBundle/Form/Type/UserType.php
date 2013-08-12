<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\ApplicantBundle\Form\Type\UserType as BaseUserType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends BaseUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $fields = array(
            'first_name',
            'last_name',
        );
        /** @var \Symfony\Component\Form\FormBuilder $val */
        foreach ($builder->all() as $key => $val) {
            if (!in_array($key, $fields)) {
                $builder->remove($key);
            }
        }



        $builder->add(
            'addresses',
            'collection',
            array(
                'type' => new UserAddressType(),
                'by_reference' => true,
                'allow_add' => true,
                'error_bubbling' => true,
                'empty_data' => true,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('user_address_new'),
                            'message' => 'error.user.address.empty',
                        )
                    ),
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'CreditJeeves\DataBundle\Entity\User',
                'validation_groups' => array('buy_report_new'),
            )
        );
    }
}
