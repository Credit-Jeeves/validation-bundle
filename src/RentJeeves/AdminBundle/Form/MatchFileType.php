<?php

namespace RentJeeves\AdminBundle\Form;

use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\CoreBundle\Translation\Translator;

class MatchFileType extends AbstractType
{
    const EMPTY_VALUE = 'empty_value';

    /**
     * @var integer
     */
    protected $numberRow;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var ImportGroupSettings
     */
    protected $settings;

    /**
     * @var array
     */
    protected $defaultValue;

    /**
     * @param Translator          $translator
     * @param ImportGroupSettings $settings
     * @param integer             $rowNumber
     * @param array               $defaultValue
     */
    public function __construct(Translator $translator, ImportGroupSettings $settings, $rowNumber, array $defaultValue)
    {
        $this->numberRow  = $rowNumber;
        $this->translator = $translator;
        $this->settings = $settings;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param  integer $i
     * @return string
     */
    public static function getFieldNameByNumber($i)
    {
        return 'column'.$i;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choicesRequired =  [
            ImportMapping::KEY_BALANCE         => $this->translator->trans('common.balance'),
            ImportMapping::KEY_RESIDENT_ID     => $this->translator->trans('import.residentId'),
            ImportMapping::KEY_TENANT_NAME     => $this->translator->trans('common.tenant_name'),
            ImportMapping::KEY_RENT            => $this->translator->trans('import.rent'),
            ImportMapping::KEY_LEASE_END       => $this->translator->trans('import.lease_end'),
            ImportMapping::KEY_MOVE_IN         => $this->translator->trans('import.move_in'),
            ImportMapping::KEY_MOVE_OUT        => $this->translator->trans('import.move_out'),
        ];

        $choicesNoneRequired = [
            ImportMapping::KEY_EMAIL                => $this->translator->trans('email'),
            ImportMapping::KEY_PAYMENT_AMOUNT       => $this->translator->trans('import.payment.amount'),
            ImportMapping::KEY_PAYMENT_DATE         => $this->translator->trans('import.payment.date'),
            ImportMapping::KEY_MONTH_TO_MONTH       => $this->translator->trans('common.month_to_month'),
            ImportMapping::KEY_USER_PHONE           => $this->translator->trans('common.tenant.cell'),
            ImportMapping::KEY_CREDITS              => $this->translator->trans('common.open_credits'),
            ImportMapping::KEY_PAYMENT_ACCEPTED     => $this->translator->trans('common.payment_accepted'),
            ImportMapping::KEY_TENANT_STATUS        => $this->translator->trans('common.tenant.status'),
            ImportMapping::KEY_EXTERNAL_LEASE_ID    => $this->translator->trans('common.lease_id'),
        ];

        if ($this->settings->getImportType() === ImportType::MULTI_GROUPS) {
            $choicesRequired[ImportMapping::KEY_GROUP_ACCOUNT_NUMBER] = $this
                ->translator
                ->trans('import.group_account_number');
        }

        if ($this->settings->getImportType() === ImportType::MULTI_PROPERTIES) {
            $choicesRequired[ImportMapping::KEY_STREET] = $this->translator->trans('common.street');
            $choicesRequired[ImportMapping::KEY_ZIP] = $this->translator->trans('common.zip');
            $choicesRequired[ImportMapping::KEY_STATE] = $this->translator->trans('common.state');
            $choicesRequired[ImportMapping::KEY_CITY] = $this->translator->trans('common.city');
            $choicesRequired[ImportMapping::KEY_UNIT_ID] = $this->translator->trans('import.unit_id');
            $choicesNoneRequired[ImportMapping::KEY_UNIT] = $this->translator->trans('import.unit');
        } else {
            $choicesRequired[ImportMapping::KEY_UNIT] = $this->translator->trans('import.unit');
        }

        $choicesRequired =  array_map(
            function ($value) {
                return $value."*";
            },
            $choicesRequired
        );

        $choices = array_merge(
            [self::EMPTY_VALUE => $this->translator->trans('common.choose_an_option')],
            $choicesRequired,
            $choicesNoneRequired
        );

        for ($i = 1; $i <= $this->numberRow; $i++) {
            $builder->add(
                self::getFieldNameByNumber($i),
                'choice',
                [
                    'choices'   => $choices,
                    'data' => (isset($this->defaultValue[$i]) ? $this->defaultValue[$i] : null),
                    'error_bubbling' => false,
                    'mapped'         => false,
                    'attr'           => [
                        'class' => 'original'
                    ]
                ]
            );
        }

        if (!empty($this->defaultValue)) {
            $builder->add('update', 'submit');
        } else {
            $builder->add('save', 'submit');
        }

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($options, $choicesRequired) {
                $used = [];
                $form = $event->getForm();
                for ($i = 1; $i <= $this->numberRow; $i++) {
                    $name = self::getFieldNameByNumber($i);
                    $field = $form->get($name);
                    $data = $field->getData();
                    if (!in_array($data, $used) && $data !== self::EMPTY_VALUE) {
                        $used[$data] = $data;
                        continue;
                    }

                    if ($data !== self::EMPTY_VALUE) {
                        $field->addError(new FormError('value.cannot.duplicate'));
                    }
                }

                $fieldsMissed = [];
                foreach ($choicesRequired as $key => $value) {
                    if (!isset($used[$key])) {
                        $fieldsMissed[] = str_replace('*', '', $value);
                    }
                }

                if (!empty($fieldsMissed)) {
                    $fieldsMissed = implode(',', $fieldsMissed);
                    $errorMessage = $this->translator->trans(
                        'you.need.map',
                        [
                            '%VALUE_WHICH_NEED_SELECT%' => $fieldsMissed,
                            '%NUMBER%'                  => count($choicesRequired)
                        ]
                    );
                    $form->addError(new FormError($errorMessage));

                    return;
                }
            }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['csrf_protection' => true, 'cascade_validation' => true]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'match_file_type';
    }
}
