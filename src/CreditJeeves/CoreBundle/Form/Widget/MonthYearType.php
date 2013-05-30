<?php
namespace CreditJeeves\CoreBundle\Form\Widget;

use Symfony\Component\Form\Extension\Core\Type\DateType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MonthYearType extends Base
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('day');
        $builder->add('day', 'hidden', array('data' => 1));
    }

    public function getName()
    {
        return 'creditjeeves_core_form_widget_datetype';
    }
}
