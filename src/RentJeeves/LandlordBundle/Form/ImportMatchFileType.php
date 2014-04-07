<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Accounting\ImportMapping as ImportMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\CoreBundle\Translation\Translator;

class ImportMatchFileType extends AbstractType
{
    const EMPTY_VALUE = 'empty_value';

    protected $numberRow;

    protected $translator;

    public function __construct($number, Translator $translator)
    {
        $this->numberRow  = $number;
        $this->translator = $translator;
    }

    public static function getFieldNameByNumber($i)
    {
        return 'column'.$i;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choicesRequired =  array(
            ImportMapping::KEY_BALANCE         => $this->translator->trans('common.balance'),
            ImportMapping::KEY_RESIDENT_ID     => $this->translator->trans('import.residentId'),
            ImportMapping::KEY_TENANT_NAME     => $this->translator->trans('tenant.name'),
            ImportMapping::KEY_UNIT            => $this->translator->trans('import.unit'),
            ImportMapping::KEY_RENT            => $this->translator->trans('import.rent'),
            ImportMapping::KEY_EMAIL           => $this->translator->trans('email'),
            ImportMapping::KEY_LEASE_END       => $this->translator->trans('import.lease_end'),
            ImportMapping::KEY_MOVE_IN         => $this->translator->trans('import.move_in'),
            ImportMapping::KEY_MOVE_OUT        => $this->translator->trans('import.move_out'),
        );

        $choicesRequired =  array_map(function($value){
            return $value."*";
        }, $choicesRequired);
        
        $choicesNoneRequired = array(
            ImportMapping::KEY_PAYMENT_AMOUNT  => $this->translator->trans('payment.amount'),
            ImportMapping::KEY_PAYMENT_DATE    => $this->translator->trans('payment.date'),
        );

        $choices = array_merge(
            array(self::EMPTY_VALUE => $this->translator->trans('choose.an.option')),
            $choicesRequired,
            $choicesNoneRequired
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
            function (FormEvent $event) use ($options, $self, $choicesRequired) {
                $used = array();
                $form = $event->getForm();
                for ($i = 1; $i <= $self->numberRow; $i++) {
                    $name = $self::getFieldNameByNumber($i);
                    $field = $form->get($name);
                    $data = $field->getData();
                    if (!in_array($data, $used) && $data !== $self::EMPTY_VALUE) {
                        $used[$data] = $data;
                        continue;
                    }

                    if ($data !== $self::EMPTY_VALUE) {
                        $field->addError(new FormError('value.cannot.duplicate'));
                    }
                }

                if (count($used) < count($choicesRequired)) {
                    $fieldsMissed = array();
                    foreach ($choicesRequired as $key => $value) {
                        if (!isset($used[$key])) {
                            $fieldsMissed[] = str_replace('*', '', $value);
                        }
                    }
                    $fieldsMissed = implode(',', $fieldsMissed);
                    $errorMessage = $self->translator->trans(
                        'you.need.map',
                        array(
                            '%VALUE_WHICH_NEED_SELECT%' => $fieldsMissed
                        )
                    );
                    $form->addError(new FormError($errorMessage));
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
