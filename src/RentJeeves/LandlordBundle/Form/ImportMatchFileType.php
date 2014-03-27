<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Report\AccountingImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportMatchFileType extends AbstractType
{
    const EMPTY_VALUE = 'empty_value';

    protected $number;

    public function __construct($number)
    {
        $this->number  = $number;
    }

    public static function getFieldNameByNumber($i)
    {
        return 'collum'.$i;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for ($i = 1; $i <= $this->number; $i++) {
            $builder->add(
                self::getFieldNameByNumber($i),
                'choice',
                array(
                    'choices'   => array(
                        self::EMPTY_VALUE => 'Choose an option',
                        AccountingImport::KEY_BALANCE         => 'Balance',
                        AccountingImport::KEY_RESIDENT_ID     => 'Resident ID',
                        AccountingImport::KEY_TENANT_NAME     => 'Tenant Name',
                        AccountingImport::KEY_UNIT            => 'Unit',
                        AccountingImport::KEY_RENT            => 'Rent',
                        AccountingImport::KEY_EMAIL           => 'Email',
                        AccountingImport::KEY_LEASE_END       => 'Lease End',
                        AccountingImport::KEY_MOVE_IN         => 'Move In',
                        AccountingImport::KEY_MOVE_OUT        => 'Move Out'
                    ),
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
            function (FormEvent $event) use ($options, $self) {
                $used = array();
                $form = $event->getForm();
                for ($i = 1; $i <= $this->number; $i++) {
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

                if (count($used) < 9) {
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
