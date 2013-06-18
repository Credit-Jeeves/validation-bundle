<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\ApplicantBundle\Form\Type\UserType as BaseUserType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends BaseUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);


        $fields = array(
            'first_name',
            'last_name',
            'street_address1',
            'city',
            'state',
            'zip',
        );
        /** @var \Symfony\Component\Form\FormBuilder $val */
        foreach ($builder->all() as $key => $val) {
            if (!in_array($key, $fields)) {
                $builder->remove($key);
            }
        }

    }



    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\User',
                'validation_groups' => array(
                    'buy_report',
                ),
            )
        );
    }

}
