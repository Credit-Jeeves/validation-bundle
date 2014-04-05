<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Accounting\ImportMapping as ImportMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportMatchFileType extends AbstractType
{
    const EMPTY_VALUE = 'empty_value';

    public $numberRow;

    public function __construct($number)
    {
        $this->numberRow  = $number;
    }

    public static function getFieldNameByNumber($i)
    {
        return 'column'.$i;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices =  array(
            self::EMPTY_VALUE => 'Choose an option',
            ImportMapping::KEY_BALANCE         => 'Balance',
            ImportMapping::KEY_RESIDENT_ID     => 'Resident ID',
            ImportMapping::KEY_TENANT_NAME     => 'Tenant Name',
            ImportMapping::KEY_UNIT            => 'Unit',
            ImportMapping::KEY_RENT            => 'Rent',
            ImportMapping::KEY_EMAIL           => 'Email',
            ImportMapping::KEY_LEASE_END       => 'Lease End',
            ImportMapping::KEY_MOVE_IN         => 'Move In',
            ImportMapping::KEY_MOVE_OUT        => 'Move Out'
        );

        for ($i = 1; $i <= $this->numberRow; $i++) {
            $builder->add(
                self::getFieldNameByNumber($i),
                'choice',
                array(
                    'choices'   => $choices,
                    'error_bubbling' => false,
                    'mapped'         => false,
                    'attr'           => array(
                        'class' => 'original'
                    )
                )
            );
        }

        $self = $this;
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($options, $self, $choices) {
                $used = array();
                $form = $event->getForm();
                for ($i = 1; $i <= $self->numberRow; $i++) {
                    $name = $self::getFieldNameByNumber($i);
                    $field = $form->get($name);
                    $data = $field->getData();
                    if (!in_array($data, $used) && $data !== $self::EMPTY_VALUE) {
                        $used[] = $data;
                        continue;
                    }

                    if ($data !== $self::EMPTY_VALUE) {
                        $field->addError(new FormError('value.cannot.duplicate'));
                    }
                }

                if (count($used) < count($choices)-1) {
                    $form->addError(new FormError('you.need.map'));
                }
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_match_file_type';
    }
}
