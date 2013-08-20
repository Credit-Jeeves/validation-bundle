<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use CreditJeeves\ApplicantBundle\Form\Type\UserAddressType as Base;
use CreditJeeves\DataBundle\Form\ChoiceList\StateChoiceList;

class UserAddressType extends Base
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('unit');
    }
}
